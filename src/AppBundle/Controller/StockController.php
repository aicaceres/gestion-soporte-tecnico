<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use ConfigBundle\Controller\UtilsController;
use AppBundle\Entity\StockHistorico;
use AppBundle\Entity\Stock;
use AppBundle\Entity\StockAjuste;
use AppBundle\Form\StockAjusteType;
use AppBundle\Entity\StockMovimiento;
use AppBundle\Form\StockMovimientoType;

/**
 * @Route("/stock")
 */
class StockController extends Controller {

    /**
     * @Route("/inventario", name="insumo_inventario")
     * @Method("GET")
     */
    public function inventarioAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_inventario');
        $em = $this->getDoctrine()->getManager();
        $depositos = $em->getRepository('ConfigBundle:Departamento')->findByDeposito(1);
        //$tipos = $em->getRepository('ConfigBundle:Tipo')->findBy(array('clase' => 'I'), array('nombre' => 'ASC'));
        $filtro = [
            'idTipo' => $request->get('idTipo'),
            'idMarca' => $request->get('idMarca'),
            'idModelo' => $request->get('idModelo')
        ];
        $tipos = $em->getRepository('AppBundle:Insumo')->combosByCriteria($filtro, 'DISTINCT t.id,t.nombre', 't.nombre', 'tipo');

        $marcas = $em->getRepository('AppBundle:Insumo')->combosByCriteria($filtro, 'DISTINCT ma.id,ma.nombre', 'ma.nombre', 'marca');
        if ($filtro['idMarca']) {
            $modelos = $em->getRepository('AppBundle:Insumo')->combosByCriteria($filtro, 'DISTINCT mo.id,mo.nombre', 'mo.nombre', 'modelo');
        }
        else {
            $modelos = NULL;
        }

        $entities = $em->getRepository('AppBundle:Insumo')->findByCriteria($filtro);
        return $this->render('AppBundle:Stock:inventario.html.twig', array(
                    'entities' => $entities,
                    'depositos' => $depositos,
                    'tipos' => $tipos,
                    'marcas' => $marcas,
                    'modelos' => $modelos,
                    'filtro' => $filtro
        ));
    }

    /**
     * IMPRESION DE inventario
     */

    /**
     * @Route("/printInventario.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_inventario")
     * @Method("POST")
     */
    public function printInventarioAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $items = $request->get('datalist');
        $criteria = array();
        parse_str($request->get('criteria'), $criteria);

        $tipo = $em->getRepository('ConfigBundle:Tipo')->find($criteria['idTipo']);
        $marca = $em->getRepository('ConfigBundle:Marca')->find($criteria['idMarca']);
        $modelo = $em->getRepository('ConfigBundle:Modelo')->find($criteria['idModelo']);
        $depositos = $em->getRepository('ConfigBundle:Departamento')->findByDeposito(1);

        $textoFiltro = [
            'tipo' => $tipo ? $tipo->getNombre() : 'Todos',
            'marca' => $marca ? $marca->getNombre() : 'Todos',
            'modelo' => $modelo ? $modelo->getNombre() : 'Todos'
        ];
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';
        //    $logo2 = __DIR__.'/../../../web/bundles/app/img/logobanner2.jpg';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:Stock:inventario.pdf.twig',
                array('items' => json_decode($items), 'filtro' => $textoFiltro, 'logo' => $logo1, 'depositos' => $depositos,
                    'search' => $request->get('searchterm')), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=inventario_insumos_' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    /** AJUSTES * */

    /**
     * @Route("/ajuste", name="insumo_ajuste")
     * @Method("GET")
     * @Template()
     */
    public function ajusteAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_ajuste');
        $depId = $request->get('depId');
        /* $hoy = new \DateTime();
          $inicio = date("d-m-Y",strtotime($hoy->format('d-m-Y')."- 30 days"));
          $desde = ($request->get('desde')) ? $request->get('desde') : $inicio;
          $hasta = ($request->get('hasta')) ? $request->get('hasta') : $hoy->format('d-m-Y'); */
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
        $em = $this->getDoctrine()->getManager();
        $depositos = $em->getRepository('ConfigBundle:Departamento')->findByDeposito(1);
        if (count($depositos) > 0) {
            if (!$depId) {
                $depId = $depositos[0]->getId();
            }
        }
        else {
            $this->addFlash('error', 'No hay depósitos');
        }
        $entities = $em->getRepository('AppBundle:StockAjuste')->findAjusteByCriteria($depId, $periodo['desde'], $periodo['hasta']);
        return $this->render('AppBundle:Stock:ajuste.html.twig', array(
                    'entities' => $entities, 'depositos' => $depositos, 'depId' => $depId, 'desde' => $periodo['desde'], 'hasta' => $periodo['hasta']
        ));
    }

    /**
     * @Route("/ajuste/new", name="insumo_ajuste_new")
     * @Method("GET")
     * @Template("AppBundle:Stock:ajusteNew.html.twig")
     */
    public function ajusteNewAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_ajuste_new');
        $depId = $request->get('depId');
        $em = $this->getDoctrine()->getManager();
        $deposito = $em->getRepository('ConfigBundle:Departamento')->find($depId);
        $entity = new StockAjuste();
        $entity->setFecha(new \DateTime);
        $entity->setDeposito($deposito);
        $form = $this->createCreateForm($entity);
        return $this->render('AppBundle:Stock:ajusteNew.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @param Insumo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(StockAjuste $entity) {
        $form = $this->createForm(new StockAjusteType(), $entity, array(
            'action' => $this->generateUrl('insumo_ajuste_create'),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/ajuste", name="insumo_ajuste_create")
     * @Method("PUT")
     * @Template("AppBundle:Stock:ajusteNew.html.twig")
     */
    public function ajusteCreateAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_ajuste_new');
        $entity = new StockAjuste();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $em->persist($entity);
                $em->flush();
                $deposito = $entity->getDeposito();
                foreach ($entity->getDetalles() as $item) {
                    // ajustar stock
                    $insumo = $item->getInsumo();
                    $stock = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($insumo->getId(), $deposito->getId());
                    if (!$stock) {
                        $stock = new Stock();
                        $stock->setInsumo($insumo);
                        $stock->setDeposito($deposito);
                        $stock->setCantidad(0);
                    }
                    //determinar cantidad si es x bulto.
                    $cantidad = $item->getCantidad();
                    if ($item->getSigno() == '+')
                        $cant = $stock->getCantidad() + $cantidad;
                    else
                        $cant = $stock->getCantidad() - $cantidad;
                    $stock->setCantidad($cant);
                    $em->persist($stock);
                    // Cargar movimiento
                    $movim = new StockHistorico();
                    $movim->setFecha($entity->getFecha());
                    $movim->setTipo('AJUSTE');
                    $movim->setSigno($item->getSigno());
                    $movim->setMovimiento($entity->getId());
                    $movim->setInsumo($insumo);
                    $movim->setStock($insumo->getStockTotal());
                    $movim->setCantidad($cantidad);
                    $movim->setDeposito($deposito);
                    $em->persist($movim);
                }
                $em->flush();
                $em->getConnection()->commit();
                return $this->redirect($this->generateUrl('insumo_ajuste'));
            }
            catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        return $this->render('AppBundle:Stock:ajusteNew.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/ajuste/{id}/show", name="insumo_ajuste_show")
     * @Method("GET")
     * @Template()
     */
    public function ajusteShowAction($id) {
        UtilsController::haveAccess($this->getUser(), 'insumo_ajuste');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:StockAjuste')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el registro del ajuste.');
        }
        return $this->render('AppBundle:Stock:ajusteShow.html.twig', array(
                    'entity' => $entity,));
    }

    /**
     * @Route("/ajuste/{id}/modalshow", name="modal_insumo_ajuste_show")
     * @Method("GET")
     * @Template()
     */
    public function modalAjusteShowAction($id) {
        UtilsController::haveAccess($this->getUser(), 'insumo_ajuste');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $entity = $em->getRepository('AppBundle:StockAjuste')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el registro del ajuste.');
        }
        $html = $this->renderView('AppBundle:Stock:modalAjusteShow.html.twig',
                array('entity' => $entity));
        return new Response($html);
    }

    /** HISTORICO * */

    /**
     * @Route("/historico", name="insumo_historico")
     * @Method("GET")
     */
    public function historicoStockAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_historico');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $filtro = [
            'idTipo' => $request->get('idTipo'),
            'idMarca' => $request->get('idMarca'),
            'idModelo' => $request->get('idModelo'),
            'desde' => $request->get('desde'),
            'hasta' => $request->get('hasta')
        ];

        $tipos = $em->getRepository('AppBundle:Insumo')->combosByCriteria($filtro, 'DISTINCT t.id,t.nombre', 't.nombre', 'tipo');

        $marcas = $em->getRepository('AppBundle:Insumo')->combosByCriteria($filtro, 'DISTINCT ma.id,ma.nombre', 'ma.nombre', 'marca');
        if ($filtro['idMarca']) {
            $modelos = $em->getRepository('AppBundle:Insumo')->combosByCriteria($filtro, 'DISTINCT mo.id,mo.nombre', 'mo.nombre', 'modelo');
        }
        else {
            $modelos = NULL;
        }
        $periodo = UtilsController::ultimoMesParaFiltro($filtro['desde'], $filtro['hasta']);
        $entities = $em->getRepository('AppBundle:StockHistorico')->findByCriteria($filtro, $periodo['desde'], $periodo['hasta']);

        self::setearDatosHistorico($entities, $em, $this);
        return $this->render('AppBundle:Stock:historico.html.twig', array(
                    'entities' => $entities,
                    'tipos' => $tipos,
                    'marcas' => $marcas,
                    'modelos' => $modelos,
                    'filtro' => $filtro
        ));
    }

    /**
     * @Route("/printHistorico.{_format}",
     * defaults = { "_format" = "pdf" },
     * name="print_historico")
     * @Method("POST")
     */
    public function printHistoricoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $criteria = array();
        parse_str($request->get('criteria'), $criteria);

        $tipo = $em->getRepository('ConfigBundle:Tipo')->find($criteria['idTipo']);
        $marca = $em->getRepository('ConfigBundle:Marca')->find($criteria['idMarca']);
        $modelo = $em->getRepository('ConfigBundle:Modelo')->find($criteria['idModelo']);

        $items = $em->getRepository('AppBundle:StockHistorico')->findByCriteria($criteria, $criteria['desde'], $criteria['hasta']);

        self::setearDatosHistorico($items, $em, $this);
        $textoFiltro = [
            'tipo' => $tipo ? $tipo->getNombre() : 'Todos',
            'marca' => $marca ? $marca->getNombre() : 'Todos',
            'modelo' => $modelo ? $modelo->getNombre() : 'Todos',
            'desde' => $criteria['desde'],
            'hasta' => $criteria['hasta'],
        ];
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';

        $facade = $this->get('ps_pdf.facade');
        $response = new Response();

        $this->render('AppBundle:Stock:historico.pdf.twig',
                array('items' => $items, 'filtro' => $textoFiltro, 'logo' => $logo1), $response);

        $xml = $response->getContent();
        $content = $facade->render($xml);
        $hoy = new \DateTime();
        return new Response($content, 200, array('content-type' => 'application/pdf',
            'Content-Disposition' => 'filename=historico_insumos_' . $hoy->format('dmY_Hi') . '.pdf'));
    }

    /** MOVIMIENTO INTERDEPOSITO * */

    /**
     * @Route("/movimiento", name="insumo_movimiento")
     * @Method("GET")
     */
    public function movimientoStockAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_movimiento');
        $em = $this->getDoctrine()->getManager();
        //$hoy = new \DateTime();
        //$inicio = date("d-m-Y",strtotime($hoy->format('d-m-Y')."- 30 days"));
        //$desde = ($request->get('desde')) ? $request->get('desde') : $inicio;
        //$hasta = ($request->get('hasta')) ? $request->get('hasta') : $hoy->format('d-m-Y');
        $em->getFilters()->disable('softdeleteable');
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
        $entities = $em->getRepository('AppBundle:StockMovimiento')->findMovimientosByCriteria($periodo['desde'], $periodo['hasta']);

        return $this->render('AppBundle:StockMovimiento:index.html.twig', array(
                    'entities' => $entities,
                    'desde' => $periodo['desde'],
                    'hasta' => $periodo['hasta']
        ));
    }

    /**
     * @Route("/movimiento/new", name="insumo_movimiento_new")
     * @Method("GET")
     * @Template("AppBundle:Stock:movimientoNew.html.twig")
     */
    public function movimientoNewAction() {
        UtilsController::haveAccess($this->getUser(), 'insumo_movimiento_new');
        $entity = new StockMovimiento();
        $entity->setFecha(new \DateTime);
        $form = $this->movimientoCreateForm($entity);
        return $this->render('AppBundle:StockMovimiento:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @param Insumo $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function movimientoCreateForm(StockMovimiento $entity) {
        $form = $this->createForm(new StockMovimientoType(), $entity, array(
            'action' => $this->generateUrl('insumo_movimiento_create'),
            'method' => 'PUT',
        ));
        return $form;
    }

    /**
     * @Route("/movimiento", name="insumo_movimiento_create")
     * @Method("PUT")
     * @Template("AppBundle:Stock:movimientoNew.html.twig")
     */
    public function movimientoCreateAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_movimiento_new');
        $entity = new StockMovimiento();
        $form = $this->movimientoCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->getConnection()->beginTransaction();
            try {
                $em->persist($entity);
                $em->flush();

                // ASENTAR MOVIMIENTO ENTRE DEPOSITOS
                $origen = $entity->getDepositoOrigen();
                $destino = $entity->getDepositoDestino();
                foreach ($entity->getDetalles() as $item) {
                    // descontar en origen
                    $insumo = $item->getInsumo();
                    $cantidad = $item->getCantidad();
                    $stockOrigen = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($insumo->getId(), $origen->getId());
                    if (!$stockOrigen) {
                        $stockOrigen = new Stock();
                        $stockOrigen->setInsumo($insumo);
                        $stockOrigen->setDeposito($origen);
                        $stockOrigen->setCantidad(0);
                    }
                    $stockOrigen->setCantidad($stockOrigen->getCantidad() - $cantidad);
                    $em->persist($stockOrigen);
                    // Cargar movimiento
                    $movim1 = new StockHistorico();
                    $movim1->setFecha($entity->getFecha());
                    $movim1->setTipo('MOVIMIENTO');
                    $movim1->setMovimiento($entity->getId());
                    $movim1->setSigno('-');
                    $movim1->setInsumo($insumo);
                    $movim1->setStock($insumo->getStockTotal());
                    $movim1->setCantidad($cantidad);
                    $movim1->setDeposito($origen);
                    $em->persist($movim1);

                    // Agregar en destino
                    $stockDestino = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($insumo->getId(), $destino->getId());
                    if (!$stockDestino) {
                        $stockDestino = new Stock();
                        $stockDestino->setInsumo($insumo);
                        $stockDestino->setDeposito($destino);
                        $stockDestino->setCantidad(0);
                    }
                    $stockDestino->setCantidad($stockDestino->getCantidad() + $cantidad);
                    $em->persist($stockDestino);
                    // Cargar movimiento
                    $movim2 = new StockHistorico();
                    $movim2->setFecha($entity->getFecha());
                    $movim2->setTipo('MOVIMIENTO');
                    $movim2->setMovimiento($entity->getId());
                    $movim2->setSigno('+');
                    $movim2->setInsumo($insumo);
                    $movim2->setStock($insumo->getStockTotal());
                    $movim2->setCantidad($cantidad);
                    $movim2->setDeposito($destino);
                    $em->persist($movim2);
                }
                $em->flush();
                $em->getConnection()->commit();
                return $this->redirect($this->generateUrl('insumo_movimiento'));
            }
            catch (\Exception $ex) {
                $this->get('session')->getFlashBag()->add('error', $ex->getMessage());
                $em->getConnection()->rollback();
            }
        }
        return $this->render('AppBundle:StockMovimiento:new.html.twig', array(
                    'entity' => $entity,
                    'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/movimiento/{id}/show", name="insumo_movimiento_show")
     * @Method("GET")
     * @Template()
     */
    public function movimientoShowAction($id) {
        UtilsController::haveAccess($this->getUser(), 'insumo_movimiento');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('AppBundle:StockMovimiento')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el registro del movimiento.');
        }
        return $this->render('AppBundle:StockMovimiento:show.html.twig', array(
                    'entity' => $entity,));
    }

    /**
     * @Route("/movimiento/{id}/modalshow", name="modal_insumo_movimiento_show")
     * @Method("GET")
     * @Template()
     */
    public function modalMovimientoShowAction($id) {
        UtilsController::haveAccess($this->getUser(), 'insumo_movimiento');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $entity = $em->getRepository('AppBundle:StockMovimiento')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('No se encuentra el registro del movimiento.');
        }
        $html = $this->renderView('AppBundle:StockMovimiento:modalshow.html.twig',
                array('entity' => $entity));
        return new Response($html);
    }

    public static function setearDatosHistorico($entities, $em, Controller $controller) {
        foreach ($entities as $historico) {
            $comp = $em->getRepository($historico->getEntidadMovimiento())->find($historico->getMovimiento());
            switch ($historico->getTipo()) {
                case 'AJUSTE':
                    $historico->nroComprobante = 'AJ ' . str_pad($comp->getId(), 6, '0', STR_PAD_LEFT);
                    $historico->urlMovimiento = $controller->generateUrl('modal_insumo_ajuste_show', array('id' => $comp->getId()));
                    break;
                case 'COMPRA':
                    $historico->nroComprobante = 'OC ' . $comp->getNroOc();
                    $historico->urlMovimiento = $controller->generateUrl('modal_compra_admin_show', array('id' => $comp->getId()));
                    break;
                case 'MOVIMIENTO':
                    $historico->nroComprobante = 'MI ' . str_pad($comp->getId(), 6, '0', STR_PAD_LEFT);
                    $historico->urlMovimiento = $controller->generateUrl('modal_insumo_movimiento_show', array('id' => $comp->getId()));
                    break;
                case 'SOPORTE':
                    if ($comp->getTarea()->getOrdenTrabajoDetalles()[0]) {
                        $historico->equipo = $em->getRepository('AppBundle:Equipo')->find($comp->getTarea()->getOrdenTrabajoDetalles()[0]->getEquipo()->getId());
                    }
                    $historico->nroComprobante = 'OT ' . $comp->getTarea()->getOrdenTrabajo()->getNroOT();
                    $historico->urlMovimiento = $controller->generateUrl('soporte_ordentrabajo_show', array('id' => $comp->getTarea()->getOrdenTrabajo()->getId()));
                    break;
                case 'ENTREGAINSUMO':
                    $historico->nroComprobante = 'EI ' . str_pad($comp->getId(), 6, '0', STR_PAD_LEFT);
                    $historico->urlMovimiento = $controller->generateUrl('modal_insumo_entrega_show', array('id' => $comp->getId()));
                    break;
                default:
                    return NULL;
            }
        }
    }

}