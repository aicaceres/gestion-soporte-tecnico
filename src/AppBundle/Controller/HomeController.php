<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ConfigBundle\Controller\UtilsController;
use AppBundle\Controller\SoporteReportesController;

class HomeController extends Controller {

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request) {
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->getUser();
            $em = $this->getDoctrine()->getManager();
            $ubicaciones = $vencimientos = null;
            $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'), false);
            if ($user->getAccess('monitoreo')) {
                $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesParaMonitoreo();
                $vencimientos = $em->getRepository('AppBundle:Vencimiento')->findAlertas();
            }
            if ($user->getActivo()) {
                if ($user->getRol()->getAdmin() || $user->getRol()->getTecnico()) {
                    $tecnicos = $em->getRepository('ConfigBundle:Usuario')->getTecnicosConOTAbierta();
                    $reqsinasignar = $em->getRepository('AppBundle:Requerimiento')->findByEstado('SIN ASIGNAR');
                    // para grafico
                    $request->attributes->set('out', 'A');
                    $otxtecnico = SoporteReportesController::getDataOTxTecnico($request);
                    return $this->render('AppBundle:Tablero:admin.html.twig',
                                    array('ubicaciones' => $ubicaciones, 'tecnicos' => $tecnicos, 'periodo' => $periodo, 'otxtecnico' => $otxtecnico,
                                        'reqsinasignar' => $reqsinasignar, 'vencimientos' => $vencimientos));
                }

                return $this->render('AppBundle:Tablero:user.html.twig');
            }
            else {
                $this->addFlash('error', 'Su usuario no se encuentra activo. Comuníquese con un administrador.');
                return $this->redirectToRoute('usuario_login');
            }
        }
        else {
            return $this->redirectToRoute('usuario_login');
        }
    }

    /**
     * @Route("/setBarcode", name="setbarcode")
     * @Method("GET")
     * @Template()
     */
    public function setBarcode() {
        return false;
        $em = $this->getDoctrine()->getManager();
        $equipos = $em->getRepository('AppBundle:Equipo')->findAll();

        try {
            //$em->getConnection()->beginTransaction();
            foreach ($equipos as $entity) {
                if (is_null($entity->getBarcode())) {
                    $entity->setBarcode(str_pad($entity->getTipo()->getId(), 3, '0', STR_PAD_LEFT) .
                            str_pad($entity->getMarca()->getId(), 3, '0', STR_PAD_LEFT) . str_pad($entity->getModelo()->getId(), 3, '0', STR_PAD_LEFT) .
                            str_pad($entity->getId(), 5, '0', STR_PAD_LEFT));
                    echo $entity->getId() . ' - ' . $entity->getBarcode() . ' <br>';
                    $em->persist($entity);
                    $em->flush();
                    //$em->getConnection()->commit();
                }
            }

            // $this->addFlash('success', 'actualizados!');
        }
        catch (\Exception $ex) {
            $em->getConnection()->rollback();
            echo UtilsController::errorMessage($ex->getErrorCode());
        }
        return true;
    }

    /**
     * @Route("/export", name="export")
     * @Template()
     */
    public function exportAction() {
        $conn = $this->get('database_connection');
        $response = new StreamedResponse(function() use($conn) {
            $handle = fopen('php://output', 'w+');
            // Add the header
            fputcsv($handle, ['nombre', 'clase'], ';');
            // Query data from database
            $results = $conn->query("SELECT nombre,clase FROM tipo");
            while ($row = $results->fetch()) {
                fputcsv($handle, array($row['nombre'], $row['clase']), ';');
            }
            fclose($handle);
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');
        return $response;
    }

    /**
     * @Route("/getDataProducto", name="get_data_producto")
     * @Method("GET")
     */
    public function getDataProducto(Request $request) {
        $codigo = $request->get('cod');
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('AppBundle:Compra')->findProductoByCodigo($codigo);
        return new JsonResponse($result);
    }

    /**
     * @Route("/getClaseTipo", name="get_clase_tipo")
     * @Method("GET")
     */
    public function getClaseTipo(Request $request) {
        $id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $result = $em->getRepository('ConfigBundle:Tipo')->find($id);

        return new Response($result->getClase());
    }

    /**
     * @Route("/getStockInsumo", name="get_stock_insumo")
     * @Method("GET")
     */
    public function getStockInsumoAction(Request $request) {
        $id = $request->get('id');
        $depid = $request->get('depid');
        $em = $this->getDoctrine()->getManager();
        $stock = $em->getRepository('AppBundle:Stock')->findInsumoDeposito($id, $depid);
        return new Response(($stock) ? $stock->getCantidad() : 0);
    }

    /**
     * @Route("/getPahtFotoModelo", name="foto_modelo")
     * @Method("GET")
     */
    public function getPahtFotoModeloAction(Request $request) {
        $url = $this->get('templating.helper.assets')->getUrl('uploads/empty.jpg');
        $id = $request->get('id');
        if ($id) {
            $em = $this->getDoctrine()->getManager();
            $modelo = $em->getRepository('ConfigBundle:Modelo')->find($id);
            if ($modelo->getWebPath()) {
                $url = $this->get('templating.helper.assets')->getUrl($modelo->getWebPath());
            }
        }
        return new Response($url);
    }

}