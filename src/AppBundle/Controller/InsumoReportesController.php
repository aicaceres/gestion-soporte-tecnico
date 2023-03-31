<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use ConfigBundle\Controller\UtilsController;

/**
 * @Route("/insumo_reporte")
 */
class InsumoReportesController extends Controller {
    private $mesesCorto = array("ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC");
    private $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
        "Septiembre", "Octubre", "Noviembre", "Diciembre");
    private $color = array('#d70206', '#00a65a', '#f4c63d', '#a748ca', '#0544d3', '#59922b', '#3c8dbc', '#6b0392', '#f05b4f', '#dda458',
        '#86797d', '#eacf7d', '#b2c326', '#6188e2', '#a748ca', '#3a01df', '#01DFD7', '#848484', '#D7DF01', '#4B610B', '#08088A');
    private $fillcolor = array('rgba(215, 2, 6, 0.5)', 'rgba(0, 166, 90, 0.5)', 'rgba(244, 198, 61, 0.5)',
        'rgba(167, 72, 202, 0.5)', 'rgba(5, 68, 211, 0.5)', 'rgba(89, 146, 43, 0.5)', 'rgba(60, 141, 188, 0.5)',
        'rgba(107, 3, 146, 0.5)', 'rgba(240, 91, 79, 0.5)', 'rgba(221, 164, 88, 0.5)', 'rgba(134, 121, 125, 0.5)',
        'rgba(234, 207, 125, 0.5)', 'rgba(178, 195, 38, 0.5)', 'rgba(97, 136, 226, 0.5)', 'rgba(167, 72, 202, 0.5)',
        'rgba(58, 1, 223, 0.5)', 'rgba(1, 223, 215, 0.5)', 'rgba(132, 132, 132, 0.5)', 'rgba(215, 223, 1, 0.5)',
        'rgba(75, 97, 11, 0.5)', 'rgba(8, 8, 138, 0.5)');

    /**
     * @Route("/solicitudes", name="insumo_reporte_solicitudes")
     * @Method("GET")
     * @Template("AppBundle:Reportes:insumo.html.twig")
     */
    public function solicitudesAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'reportes_insumo');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_reportes_insumo');
        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                $filtro = array(
                    'selTipos' => $request->get('selTipos'),
                    'selUbicaciones' => $request->get('selUbicaciones'),
                    'selEdificios' => $request->get('selEdificios'),
                    'selDepartamento' => $request->get('selDepartamento'),
                    'desde' => $periodo['desde'],
                    'hasta' => $periodo['hasta'],
                );
                break;
            default:
                //desde paginacion, se usa session
                if ($sessionFiltro) {
                    $periodo = UtilsController::ultimoMesParaFiltro($sessionFiltro['desde'], $sessionFiltro['hasta']);
                    $filtro = array(
                        'selTipos' => $sessionFiltro['selTipos'],
                        'selUbicaciones' => $sessionFiltro['selUbicaciones'],
                        'selEdificios' => $sessionFiltro['selEdificios'],
                        'selDepartamento' => $sessionFiltro['selDepartamento'],
                        'desde' => $periodo['desde'],
                        'hasta' => $periodo['hasta'],
                    );
                }
                else {
                    $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                    $filtro = array('selUbicaciones' => 0, 'selEdificios' => 0, 'selDepartamento' => 0, 'selTipos' => NULL,
                        'desde' => $periodo['desde'], 'hasta' => $periodo['hasta']);
                }
                break;
        }
        $session->set('filtro_reportes_insumo', $filtro);
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->findByDeletedAt(null);
        $edificios = null;
        $departamentos = null;
        $sectores = $em->getRepository('ConfigBundle:Departamento')->findAllOrdenados();
        if ($filtro['selUbicaciones']) {
            $edificios = $em->getRepository('ConfigBundle:Edificio')->findEdificiosByUbicaciones($filtro['selUbicaciones'], $this->getUser()->getId());
            $sectores = $em->getRepository('ConfigBundle:Departamento')->findDepartamentosByUbicaciones($filtro['selUbicaciones']);
            if ($filtro['selEdificios']) {
                $sectores = $departamentos = $em->getRepository('ConfigBundle:Departamento')->findDepartamentosByEdificios($filtro['selEdificios']);
            }
        }

        $tiposInsumos = $em->getRepository('ConfigBundle:Tipo')->findBy(array('clase' => 'I', 'subclase' => 'HARDWARE'), array('nombre' => 'ASC'));

        return array(
            'filtro' => $filtro,
            'tiposInsumos' => $tiposInsumos,
            'ubicaciones' => $ubicaciones,
            'edificios' => $edificios,
            'departamentos' => $departamentos,
            'datos' => $this->getDatosReporteInsumoxSector($em, $filtro, $tiposInsumos, $sectores)
        );
    }

    /**
     * @Route("/entregas", name="insumo_reporte_entregas")
     * @Method("GET")
     * @Template("AppBundle:Reportes:entregas.html.twig")
     */
    public function entregasAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'reportes_insumo');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_reportes_entregas');

        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                $filtro = array(
                    'selTipo' => $request->get('selTipo'),
                    'selUbicaciones' => $request->get('selUbicaciones'),
                    'selEdificios' => $request->get('selEdificios'),
                    'selDepartamento' => $request->get('selDepartamento'),
                    'desde' => $periodo['desde'],
                    'hasta' => $periodo['hasta'],
                );
                break;
            default:
                //desde paginacion, se usa session
                if ($sessionFiltro) {
                    $periodo = UtilsController::ultimoMesParaFiltro($sessionFiltro['desde'], $sessionFiltro['hasta']);
                    $filtro = array(
                        'selTipo' => $sessionFiltro['selTipo'],
                        'selUbicaciones' => $sessionFiltro['selUbicaciones'],
                        'selEdificios' => $sessionFiltro['selEdificios'],
                        'selDepartamento' => $sessionFiltro['selDepartamento'],
                        'desde' => $periodo['desde'],
                        'hasta' => $periodo['hasta'],
                    );
                }
                else {
                    $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'));
                    $filtro = array('selUbicaciones' => 0, 'selEdificios' => 0, 'selDepartamento' => 0, 'selTipo' => 0,
                        'desde' => $periodo['desde'], 'hasta' => $periodo['hasta']);
                }
                break;
        }
        $session->set('filtro_reportes_entregas', $filtro);
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->findByDeletedAt(null);
        $edificios = null;
        $departamentos = null;
        $sectores = $em->getRepository('ConfigBundle:Departamento')->findAllOrdenados();
        if ($filtro['selUbicaciones']) {
            $edificios = $em->getRepository('ConfigBundle:Edificio')->findEdificiosByUbicaciones($filtro['selUbicaciones'], $this->getUser()->getId());
            $sectores = $em->getRepository('ConfigBundle:Departamento')->findDepartamentosByUbicaciones($filtro['selUbicaciones']);
            if ($filtro['selEdificios']) {
                $sectores = $departamentos = $em->getRepository('ConfigBundle:Departamento')->findDepartamentosByEdificios($filtro['selEdificios']);
            }
        }

        $tiposInsumos = $em->getRepository('ConfigBundle:Tipo')->findBy(array('clase' => 'I', 'subclase' => 'INSUMO'), array('nombre' => 'ASC'));

        return array(
            'filtro' => $filtro,
            'tiposInsumos' => $tiposInsumos,
            'ubicaciones' => $ubicaciones,
            'edificios' => $edificios,
            'departamentos' => $departamentos,
            'datos' => $this->getDatosReporteEntregasxSector($em, $filtro, $sectores)
        );
    }

    /**
     * @Route("/printReporteInsumos", name="print_reporte_insumos")
     * @Method("POST")
     * @Template()
     */
    public function printReporteInsumos(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $filtro = $session->get('filtro_reportes_insumo');
        $tipoSalida = $request->get('tiposalida');
        $nombreReporte = $request->get('reporte');
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->findByDeletedAt(null);
        $edificios = null;
        $departamentos = null;
        $sectores = $em->getRepository('ConfigBundle:Departamento')->findAllOrdenados();
        if ($filtro['selUbicaciones']) {
            $edificios = $em->getRepository('ConfigBundle:Edificio')->findEdificiosByUbicaciones($filtro['selUbicaciones'], $this->getUser()->getId());
            $sectores = $em->getRepository('ConfigBundle:Departamento')->findDepartamentosByUbicaciones($filtro['selUbicaciones']);
            if ($filtro['selEdificios']) {
                $sectores = $departamentos = $em->getRepository('ConfigBundle:Departamento')->findDepartamentosByEdificios($filtro['selEdificios']);
            }
        }

        $tiposInsumos = $em->getRepository('ConfigBundle:Tipo')->findBy(array('clase' => 'I', 'subclase' => 'HARDWARE'), array('nombre' => 'ASC'));

        $textoFiltro = array('Todos');
        $hoy = new \DateTime();

        // guarda la imágen del gráfico en directorio temporal
        $baseFromJavascript = $request->get('grafico');
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $baseFromJavascript));
        $tmpfname = tempnam(sys_get_temp_dir(), 'x');
        file_put_contents($tmpfname, $data);
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';
        switch ($nombreReporte) {
            case 'insumoxsector':
                $textoFiltro = array('ubicaciones' => ($filtro['selUbicaciones']) ? $this->getTextoFiltro($em, $filtro['selUbicaciones']) : 'Todos',
                    'edificios' => ($filtro['selEdificios']) ? $this->getTextoFiltro($em, $filtro['selEdificios'], 'Edificio') : 'Todos',
                    'departamentos' => ($filtro['selDepartamento']) ? $this->getTextoFiltro($em, $filtro['selDepartamento'], 'Departamento') : 'Todos',
                    'tiposEquipo' => ($filtro['selTipos']) ? $this->getTextoFiltro($em, $filtro['selTipos'], 'tiposEquipo') : 'Todos',
                    'desde' => $filtro['desde'], 'hasta' => $filtro['hasta']);
                $datos = $this->getDatosReporteInsumoxSector($em, $filtro, $tiposInsumos, $sectores);
                switch ($tipoSalida) {
                    case 'pdf':
                        $plantilla = 'AppBundle:Reportes:print_insumo_xsector.pdf.twig';
                        $arraydata = array('logo' => $logo1, 'grafico' => $tmpfname, 'datos' => $datos, 'filtro' => $textoFiltro);
                        $filename = 'informe_insumosxsector_';
                        break;
                    case 'xls':
                        $plantilla = 'AppBundle:Reportes:export_insumo_xsector-xls.html.twig';
                        $arraydata = array('datos' => $datos, 'filtro' => $textoFiltro);
                        $filename = $fileName = 'Reporte_InsumosxSector_';
                        break;
                }
                break;
        }
        switch ($tipoSalida) {
            case 'pdf':
                $facade = $this->get('ps_pdf.facade');
                $response = new Response();
                $this->render($plantilla, $arraydata, $response);
                $xml = $response->getContent();
                $content = $facade->render($xml);
                return new Response($content, 200, array('content-type' => 'application/pdf',
                    'Content-Disposition' => 'filename=' . $filename . $hoy->format('dmY_Hi') . '.pdf'));
            case 'xls':
                $partial = $this->renderView($plantilla, $arraydata);
                $response = new Response();
                $response->setStatusCode(200);
                $response->headers->set('Content-Type', 'application/vnd.ms-excel; charset=UTF-8');
                $response->headers->set('Content-Disposition', 'filename="' . $filename . $hoy->format('dmY_Hi') . '.xls"');
                $response->setContent($partial);
                return $response;
        }
    }

    /**
     * @Route("/mesaentrada", name="insumo_reporte_mesaentrada")
     * @Method("GET")
     * @Template("AppBundle:Reportes:mesaentrada.html.twig")
     */
    public function mesaentradaAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'reportes_insumo');
        $em = $this->getDoctrine()->getManager();
        $em->getFilters()->disable('softdeleteable');
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_reportes_mesaentrada');

        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $periodo = $this->getPeriodoParaFiltro($request->get('desde'), $request->get('hasta'));
                $filtro = array(
                    'selTipos' => $request->get('selTipos'),
                    'selUbicaciones' => $request->get('selUbicaciones'),
                    'selEdificios' => $request->get('selEdificios'),
                    'selDepartamento' => $request->get('selDepartamento'),
                    'desde' => $periodo['desde'],
                    'hasta' => $periodo['hasta'],
                );
                break;
            default:
                if ($sessionFiltro) {
                    $periodo = $this->getPeriodoParaFiltro($sessionFiltro['desde'], $sessionFiltro['hasta']);
                    $filtro = array(
                        'selTipos' => $sessionFiltro['selTipos'],
                        'selUbicaciones' => $sessionFiltro['selUbicaciones'],
                        'selEdificios' => $sessionFiltro['selEdificios'],
                        'selDepartamento' => $sessionFiltro['selDepartamento'],
                        'desde' => $periodo['desde'],
                        'hasta' => $periodo['hasta'],
                    );
                }
                else {
                    $periodo = $this->getPeriodoParaFiltro($request->get('desde'), $request->get('hasta'));
                    $filtro = array('selUbicaciones' => 0, 'selEdificios' => 0, 'selDepartamento' => 0, 'selTipos' => NULL,
                        'desde' => $periodo['desde'], 'hasta' => $periodo['hasta']);
                }
                break;
        }
        $session->set('filtro_reportes_mesaentrada', $filtro);
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->findByDeletedAt(null);
        $edificios = null;
        $departamentos = null;
        $sectores = $em->getRepository('ConfigBundle:Departamento')->findAllOrdenados();
        if ($filtro['selUbicaciones']) {
            $edificios = $em->getRepository('ConfigBundle:Edificio')->findEdificiosByUbicaciones($filtro['selUbicaciones'], $this->getUser()->getId());
            $sectores = $em->getRepository('ConfigBundle:Departamento')->findDepartamentosByUbicaciones($filtro['selUbicaciones']);
            if ($filtro['selEdificios']) {
                $sectores = $departamentos = $em->getRepository('ConfigBundle:Departamento')->findDepartamentosByEdificios($filtro['selEdificios']);
            }
        }

        $tiposInsumos = $em->getRepository('ConfigBundle:Tipo')->findBy(array('clase' => 'I', 'subclase' => 'INSUMO'), array('nombre' => 'ASC'));

        return array(
            'filtro' => $filtro,
            'tiposInsumos' => $tiposInsumos,
            'ubicaciones' => $ubicaciones,
            'edificios' => $edificios,
            'departamentos' => $departamentos,
            'datos' => $this->getDatosReporteMesaEntrada($em, $filtro, $sectores)
        );
    }

    private function getPeriodoParaFiltro($desde, $hasta) {
        $hoy = new \DateTime();
        $inicio = date("d-m-Y", strtotime('first day of January', time()));
        $ini = ($desde) ? $desde : $inicio;
        $fin = ($hasta) ? $hasta : $hoy->format('d-m-Y');
        return array('desde' => $ini, 'hasta' => $fin);
    }

    private function getDatosReporteMesaEntrada($em, $filtro) {
        // Reporte por sector, tipo de insumo y mes
        $movimientos = $em->getRepository('AppBundle:InsumoEntrega')->getMovimientosMesaEntrada($filtro);
        // armar array de meses según filtro periodo
        $meses = UtilsController::getMesesEnPeriodo($filtro);
        // obtener los tipos de insumo
        $tiposInsumos = array_unique(array_column($movimientos, 'insumo'));
        $sectores = array_unique(array_column($movimientos, 'sector'));
        arsort($sectores);
        $dataset = $data = $tabla = array();
        /**
         *  GRAFICO
         */
        // crear estructura del dataset
        $i = 0;
        foreach ($tiposInsumos as $tipo) {
            // data por mes
            foreach ($meses as $mes) {
                $data[$mes] = 0;
            }
            $i = ($i < 20) ? ++$i : 0;
            $dataset[$tipo] = array('label' => $tipo, 'maxBarThickness' => 50, 'backgroundColor' => $this->fillcolor[$i], 'borderColor' => $this->color[$i], 'borderWidth' => 1, 'data' => $data);
        }
        // cargar data de cada insumo y periodo
        foreach ($movimientos as $mov) {
            $mes = $mov['fecha']->format('m-Y');
            $dataset[$mov['insumo']]['data'][$mes] = floatval($dataset[$mov['insumo']]['data'][$mes]) + floatval($mov['cantidad']);
        }
        // quitar keys de data dentro del dataset
        foreach ($dataset as &$item) {
            $values = array_values($item['data']);
            $item['data'] = $values;
        }
        // cambiar mes en formate numero por texto corto
        foreach ($meses as $mes) {
            $arr = split('-', $mes);
            $labels[] = $this->mesesCorto[intval($arr[0]) - 1] . ' ' . $arr[1];
        }
        /**
         * TABLA
         */
        $arrTotal = array('tipo' => 'T', 'nombre' => 'TOTALES', 'id' => null, 'padre' => null, 'recuento' => $data, 'total' => 0);
        foreach ($sectores as $sectorKey => $sector) {
            $arrSector = array('tipo' => 'S', 'nombre' => $sector, 'id' => 'S' . $sectorKey, 'padre' => NULL, 'recuento' => $data, 'total' => 0);
            foreach ($tiposInsumos as $tipoKey => $tipo) {
                $arrTipo = array('tipo' => 'M', 'nombre' => $tipo, 'id' => 'M' . $sectorKey . $tipoKey, 'padre' => 'S' . $sectorKey, 'recuento' => $data, 'total' => 0);
                foreach ($movimientos as $mov) {
                    if ($mov['insumo'] == $tipo && $mov['sector'] == $sector) {
                        // contar en el mes que corresponda
                        $mes = $mov['fecha']->format('m-Y');
                        $arrTipo['recuento'][$mes] = floatval($arrTipo['recuento'][$mes]) + floatval($mov['cantidad']);
                        $arrTipo['total'] = floatval($arrTipo['total']) + floatval($mov['cantidad']);
                    }
                }
                if ($arrTipo['total'] > 0) {
                    array_unshift($tabla, $arrTipo);
                    foreach ($arrTipo['recuento'] as $key => $value) {
                        $arrSector['recuento'][$key] = floatval($arrSector['recuento'][$key]) + floatval($arrTipo['recuento'][$key]);
                    }
                    $arrSector['total'] = floatval($arrSector['total']) + floatval($arrTipo['total']);
                }
            }
            if ($arrSector['total'] > 0) {
                array_unshift($tabla, $arrSector);
                foreach ($arrSector['recuento'] as $key => $value) {
                    $arrTotal['recuento'][$key] = floatval($arrTotal['recuento'][$key]) + floatval($arrSector['recuento'][$key]);
                }
                $arrTotal['total'] = floatval($arrTotal['total']) + floatval($arrSector['total']);
            }
        }

        array_push($tabla, $arrTotal);
        return array('dataset' => array_values($dataset), 'labels' => $labels, 'meses' => $meses, 'tabla' => $tabla);
    }

    /*
     * FUNCIONES ADICIONALES
     */

    private function getDatosReporteInsumoxSector($em, $filtro, $tiposInsumos, $sectores) {
        // Reporte por Sector, tipo incidencia y tipo de equipos
        if ($em->getFilters()->isEnabled('softdeleteable')) {
            $em->getFilters()->disable('softdeleteable');
        }
        $movimientos = $em->getRepository('AppBundle:Stock')->getMovimientosInsumosParaSoporte($filtro);

        $recuento = $i = 0;
        $tabla = $dataset = array();
        $arrTotal = array('tipo' => 'T', 'nombre' => 'TOTALES', 'id' => null, 'padre' => null, 'recuento' => 0);
        foreach ($tiposInsumos as $tipo) {
            $arrTipo = array('tipo' => 'S', 'nombre' => $tipo->getNombre(), 'id' => 'S' . $tipo->getId(), 'padre' => NULL, 'recuento' => 0);
            foreach ($sectores as $sector) {
                $nombre = $sector['abreviatura'] . ' - ' . $sector['edifnombre'] . ' - ' . $sector['nombre'];
                $arrSector = array('tipo' => 'M', 'nombre' => $nombre, 'id' => 'M' . $tipo->getId() . $sector['id'], 'padre' => 'S' . $tipo->getId(), 'recuento' => 0);
                foreach ($movimientos as $mov) {
                    $insxtarea = $em->getRepository('AppBundle:InsumoxTarea')->find($mov->getMovimiento());
                    $solic = $insxtarea->getTarea()->getOrdenTrabajo()->getRequerimiento()->getSolicitante()->getId();
                    if ($mov->getInsumo()->getTipo()->getId() == $tipo->getId() && $solic == $sector['id']) {
                        $arrSector['recuento'] += $mov->getCantidad();
                    }
                }
                if ($arrSector['recuento'] > 0) {
                    array_unshift($tabla, $arrSector);
                    $arrTipo['recuento'] += $arrSector['recuento'];
                }
            }
            if ($arrTipo['recuento'] > 0) {
                array_unshift($tabla, $arrTipo);

                $arrTotal['recuento'] += $arrTipo['recuento'];
                $i = ($i < 20) ? ++$i : 0;
                $dataset[] = array('label' => $tipo->getNombre(), 'maxBarThickness' => 50, 'backgroundColor' => $this->fillcolor[$i], 'borderColor' => $this->color[$i], 'borderWidth' => 1, 'data' => array($arrTipo['recuento']));
            }
        }
        array_push($tabla, $arrTotal);
        $total = $arrTotal['recuento'];
        $label = array('PERÍODO ' . $filtro['desde'] . ' al ' . $filtro['hasta']);
        return array('dataset' => $dataset, 'labels' => $label, 'tabla' => $tabla, 'total' => $total);
    }

    private function getTextoFiltro($em, $datos, $tabla = 'Ubicacion') {
        $cadena = '';
        foreach ($datos as $i => $dato) {
            $texto = $em->getRepository('ConfigBundle:' . $tabla)->find($dato);
            $cadena = $cadena . $texto;
            if ($i < count($datos) - 1) {
                $cadena = $cadena . ' - ';
            }
        }
        return $cadena;
    }

    /**
     *
     * Entregas de insumos
     */
    private function getDatosReporteEntregasxSector($em, $filtro, $sectores) {
        // Reporte por Sector, tipo incidencia y tipo de equipos
        if ($em->getFilters()->isEnabled('softdeleteable')) {
            $em->getFilters()->disable('softdeleteable');
        }
        $movimientos = $em->getRepository('AppBundle:Stock')->getMovimientosEntregaInsumos($filtro);
        $tiposInsumo = $em->getRepository('AppBundle:Stock')->getMovimientosEntregaInsumosDistinct($filtro);

        $i = 0;
        $tabla = $dataset = array();
        foreach ($tiposInsumo as $tipo) {
            $i = ($i < 20) ? ++$i : 0;
            $dataset[$tipo['nombre']] = array('label' => $tipo['nombre'], 'maxBarThickness' => 50, 'backgroundColor' => $this->fillcolor[$i], 'borderColor' => $this->color[$i], 'borderWidth' => 1, 'data' => array(0));
        }

        $arrTotal = array('tipo' => 'T', 'nombre' => 'TOTALES', 'id' => null, 'padre' => null, 'recuento' => 0);

        foreach ($sectores as $sector) {
            $nombre = $sector['abreviatura'] . ' - ' . $sector['edifnombre'] . ' - ' . $sector['nombre'];
            $arrSector = array('tipo' => 'S', 'nombre' => $nombre, 'id' => 'S' . $sector['id'], 'padre' => NULL, 'recuento' => 0);
            foreach ($tiposInsumo as $tipo) {
                $arrTipo = array('tipo' => 'M', 'nombre' => $tipo['nombre'], 'id' => 'M' . $sector['id'] . $tipo['id'], 'padre' => 'S' . $sector['id'], 'recuento' => 0);
                foreach ($movimientos as $mov) {
                    $entrega = $em->getRepository('AppBundle:InsumoEntrega')->find($mov->getMovimiento());
                    $solic = $entrega->getSolicitante()->getId();
                    // recorrer entrega
                    if ($mov->getInsumo()->getId() == $tipo['id'] && $solic == $sector['id']) {
                        $arrTipo['recuento'] += $mov->getCantidad();
                    }
                }
                if ($arrTipo['recuento'] > 0) {
                    array_unshift($tabla, $arrTipo);
                    $arrSector['recuento'] += $arrTipo['recuento'];

                    $dataset[$tipo['nombre']]['data'] = array($dataset[$tipo['nombre']]['data'][0] + $arrTipo['recuento']);
                }
            }
            if ($arrSector['recuento'] > 0) {
                array_unshift($tabla, $arrSector);
                $arrTotal['recuento'] += $arrSector['recuento'];
            }
        }
        array_push($tabla, $arrTotal);
        $total = $arrTotal['recuento'];
        $label = array('PERÍODO ' . $filtro['desde'] . ' al ' . $filtro['hasta']);
        return array('dataset' => array_values($dataset), 'labels' => $label, 'tabla' => $tabla, 'total' => $total);
    }

}