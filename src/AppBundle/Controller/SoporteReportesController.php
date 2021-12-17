<?php

namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use ConfigBundle\Controller\UtilsController;

/**
 * @Route("/soporte_reportes")
 */
class SoporteReportesController extends Controller {
    private $mesesCorto = array("ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC");
    private $meses = array("Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto",
        "Septiembre", "Octubre", "Noviembre", "Diciembre");

    const MESCORTO = array("ENE", "FEB", "MAR", "ABR", "MAY", "JUN", "JUL", "AGO", "SEP", "OCT", "NOV", "DIC");
    const COLOR = array('#d70206', '#00a65a', '#f4c63d', '#a748ca', '#0544d3', '#59922b', '#3c8dbc', '#6b0392', '#f05b4f', '#dda458',
        '#86797d', '#eacf7d', '#b2c326', '#6188e2', '#a748ca', '#3a01df', '#01DFD7', '#848484', '#D7DF01', '#4B610B', '#08088A');
    const FILLCOLOR = array('rgba(215, 2, 6, 0.5)', 'rgba(0, 166, 90, 0.5)', 'rgba(244, 198, 61, 0.5)',
        'rgba(167, 72, 202, 0.5)', 'rgba(5, 68, 211, 0.5)', 'rgba(89, 146, 43, 0.5)', 'rgba(60, 141, 188, 0.5)',
        'rgba(107, 3, 146, 0.5)', 'rgba(240, 91, 79, 0.5)', 'rgba(221, 164, 88, 0.5)', 'rgba(134, 121, 125, 0.5)',
        'rgba(234, 207, 125, 0.5)', 'rgba(178, 195, 38, 0.5)', 'rgba(97, 136, 226, 0.5)', 'rgba(167, 72, 202, 0.5)',
        'rgba(58, 1, 223, 0.5)', 'rgba(1, 223, 215, 0.5)', 'rgba(132, 132, 132, 0.5)', 'rgba(215, 223, 1, 0.5)',
        'rgba(75, 97, 11, 0.5)', 'rgba(8, 8, 138, 0.5)');

    /**
     * @Route("/detallado", name="soporte_reportes_detallado")
     * @Method("GET")
     * @Template("AppBundle:Reportes:index.html.twig")
     */
    public function indexAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'reportes_soporte');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_reportes_soporte');
        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'), false);
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
                    $periodo = UtilsController::ultimoMesParaFiltro($sessionFiltro['desde'], $sessionFiltro['hasta'], false);
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
                    $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'), false);
                    $filtro = array('selUbicaciones' => 0, 'selEdificios' => 0, 'selDepartamento' => 0, 'selTipos' => NULL,
                        'desde' => $periodo['desde'], 'hasta' => $periodo['hasta']);
                }
                break;
        }
        $session->set('filtro_reportes_soporte', $filtro);
        $userId = $this->getUser()->getId();
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesPermitidas($userId);

        $edificios = null;
        $departamentos = null;
        if ($filtro['selUbicaciones']) {
            $edificios = $em->getRepository('ConfigBundle:Edificio')->findEdificiosByUbicaciones($filtro['selUbicaciones'], $userId);
            if ($filtro['selEdificios']) {
                $departamentos = $em->getRepository('ConfigBundle:Departamento')->findDepartamentosByEdificios($filtro['selEdificios']);
            }
        }
        $tiposSoporte = $em->getRepository('ConfigBundle:TipoSoporte')->findAll();
        $tiposEquipos = $em->getRepository('ConfigBundle:Tipo')->findByClase('E');
        // Reporte por Sector, tipo incidencia y tipo de equipos
        $requerimientos = $em->getRepository('AppBundle:Requerimiento')->getRequerimientosParaReporte($filtro, $userId);

        $dataEqxIncidenciaySector = $this->getDatosDetalladoEqxIncidenciaySector($em, $filtro, $requerimientos, $tiposSoporte, $tiposEquipos);
        $dataEqxTipoIncidencia = $this->getDatosDetalladoEqxTipoIncidencia($em, $filtro, $requerimientos, $tiposSoporte, $tiposEquipos);


        /*
          $recuento = 0;
          $tablaSectorTipoIncidencia = array();
          $arrTotal = array('tipo' => 'T','nombre' => 'TOTALES','id'=>null,'padre'=>null,'recuento' => 0);
          foreach ($sectores as $sector) {
          $arrSector = array('tipo' => 'S', 'nombre' => $sector->getNombreCompleto(), 'id' => 'S' . $sector->getId(), 'padre' => null, 'recuento' => 0);
          foreach ($tiposSoporte as $tipoSop) {
          if ($filtro['selTipos'] == NULL || in_array($tipoSop->getId(), $filtro['selTipos'])) {
          $arrTipoSop = array('tipo' => 'M', 'nombre' => $tipoSop->getNombre(), 'id' => 'M' . $sector->getId() . $tipoSop->getId(), 'padre' => 'S' . $sector->getId(), 'recuento' => 0);
          foreach ($tiposEquipos as $tipoeq) {
          $arrTipoEq = array('tipo' => 'I', 'nombre' => $tipoeq->getNombre(), 'id' => 'I' . $sector->getId() . $tipoSop->getId() . $tipoeq->getId(), 'padre' => 'M' . $sector->getId() . $tipoSop->getId(), 'recuento' => 0);
          foreach ($requerimientos as $req) {
          if ($req->getTipoSoporte()) {
          if ($req->getSolicitante()->getId() == $sector->getId() &&
          $req->getTipoSoporte()->getId() == $tipoSop->getId()) {
          //$recuento = $req->getCuentaDeEquipos($tipoeq->getId());
          $recuento = 1;
          $arrTipoEq['recuento'] += $recuento;
          }
          }
          }
          if ($arrTipoEq['recuento'] > 0) {
          array_unshift($tablaSectorTipoIncidencia, $arrTipoEq);
          $arrTipoSop['recuento'] += $arrTipoEq['recuento'];
          }
          }
          if ($arrTipoSop['recuento'] > 0) {
          array_unshift($tablaSectorTipoIncidencia, $arrTipoSop);
          $arrSector['recuento'] += $arrTipoSop['recuento'];
          }
          } else {
          continue;
          }
          }
          if ($arrSector['recuento'] > 0) {
          array_unshift($tablaSectorTipoIncidencia, $arrSector);
          $arrTotal['recuento'] += $arrSector['recuento'];
          }
          }
          array_push($tablaSectorTipoIncidencia, $arrTotal);
          $totalSectorTipoIncidencia = $arrTotal['recuento'];
         */

        /* echo '<pre>';
          var_dump($tablaTipoIncidenciaSector);
          echo '</pre>';
          die; */
        return array(
            'filtro' => $filtro,
            'meses' => $this->meses,
            'tiposSoporte' => $tiposSoporte,
            'ubicaciones' => $ubicaciones,
            'edificios' => $edificios,
            'departamentos' => $departamentos,
            'eqxincidencia' => $dataEqxTipoIncidencia,
            'tablaTipoIncidenciaSector' => $dataEqxIncidenciaySector['tabla'],
            'totalTipoIncidenciaSector' => $dataEqxIncidenciaySector['total']
        );
    }

    /**
     * @Route("/anual", name="soporte_reportes_anual")
     * @Method("GET")
     * @Template("AppBundle:Reportes:anual.html.twig")
     */
    public function anualAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'reportes_soporte');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_reportes_soporte_anual');
        $hoy = new \DateTime();
        $anio = ($request->get('anio') == 0) ? $hoy->format('Y') : $request->get('anio');
        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $filtro = array(
                    'selTipos' => $request->get('selTipos'),
                    'selUbicaciones' => $request->get('selUbicaciones'),
                    'selEdificios' => $request->get('selEdificios'),
                    'selDepartamento' => $request->get('selDepartamento'),
                    'anio' => $anio
                );
                break;
            default:
                //desde paginacion, se usa session
                if ($sessionFiltro) {
                    $filtro = array(
                        'selTipos' => $sessionFiltro['selTipos'],
                        'selUbicaciones' => $sessionFiltro['selUbicaciones'],
                        'selEdificios' => $sessionFiltro['selEdificios'],
                        'selDepartamento' => $sessionFiltro['selDepartamento'],
                        'anio' => $sessionFiltro['anio']
                    );
                }
                else {
                    $filtro = array('selUbicaciones' => 0, 'selEdificios' => 0, 'selDepartamento' => 0, 'selTipos' => NULL, 'anio' => $hoy->format('Y'));
                }
                break;
        }
        $session->set('filtro_reportes_soporte_anual', $filtro);
        $userId = $this->getUser()->getId();
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesPermitidas($userId);

        $edificios = null;
        $departamentos = null;
        if ($filtro['selUbicaciones']) {
            $edificios = $em->getRepository('ConfigBundle:Edificio')->findEdificiosByUbicaciones($filtro['selUbicaciones'], $userId);
            if ($filtro['selEdificios']) {
                $departamentos = $em->getRepository('ConfigBundle:Departamento')->findDepartamentosByEdificios($filtro['selEdificios']);
            }
        }
        //$departamentos = $em->getRepository('ConfigBundle:Departamento')->getNombreCompleto();
        $tiposSoporte = $em->getRepository('ConfigBundle:TipoSoporte')->findAll();
        $reqxIncidencia = $this->getDatosAnualReqxIncidencia($em, $filtro);
        return array(
            'filtro' => $filtro,
            'meses' => $this->meses,
            'ubicaciones' => $ubicaciones,
            'edificios' => $edificios,
            'departamentos' => $departamentos,
            'tiposSoporte' => $tiposSoporte,
            'reqxEstados' => $this->getDatosAnualReqxEstado($em, $filtro),
            'reqxIncidencia' => $reqxIncidencia,
            'fillcolor' => self::FILLCOLOR
        );
    }

    /**
     * @Route("/resumen", name="soporte_reportes_resumen")
     * @Method("GET")
     * @Template("AppBundle:Reportes:resumen.html.twig")
     */
    public function resumenAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'reportes_soporte');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_reportes_soporte_resumen');
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
        $session->set('filtro_reportes_soporte_resumen', $filtro);

        //$ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->findAll();
        $userId = $this->getUser()->getId();
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesPermitidas($userId);

        $edificios = null;
        $departamentos = null;
        if ($filtro['selUbicaciones']) {
            $edificios = $em->getRepository('ConfigBundle:Edificio')->findEdificiosByUbicaciones($filtro['selUbicaciones'], $userId);
            if ($filtro['selEdificios']) {
                $departamentos = $em->getRepository('ConfigBundle:Departamento')->findDepartamentosByEdificios($filtro['selEdificios']);
            }
        }
        $tiposSoporte = $em->getRepository('ConfigBundle:TipoSoporte')->findAll();

        // Resumen x sector
        $dataResumen_reqxSolicitante = $this->getDatosResumenReqxSolicitante($em, $filtro, $ubicaciones);

        // Resumen x tipo de incidencia
        $dataResumen_reqxIncidencia = $this->getDatosResumenReqxIncidencia($em, $filtro);

        return array(
            'filtro' => $filtro,
            'ubicaciones' => $ubicaciones,
            'edificios' => $edificios,
            'departamentos' => $departamentos,
            'tiposSoporte' => $tiposSoporte,
            'fillcolor' => self::FILLCOLOR,
            'bordercolor' => self::COLOR,
            'dataResumen_reqxSolicitante' => $dataResumen_reqxSolicitante,
            'dataResumen_reqxIncidencia' => $dataResumen_reqxIncidencia,
        );
    }

    /**
     * @Route("/printReporte", name="print_reporte")
     * @Method("POST")
     * @Template()
     */
    public function printReporte(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $tipoSalida = $request->get('tiposalida');
        $nombreReporte = $request->get('reporte');

        $userId = $this->getUser()->getId();
        $ubicaciones = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesPermitidas($userId);

        $textoFiltro = array('Todos');
        $hoy = new \DateTime();

        // guarda la imágen del gráfico en directorio temporal
        $baseFromJavascript = $request->get('grafico');
        $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $baseFromJavascript));
        $tmpfname = tempnam(sys_get_temp_dir(), 'x');
        file_put_contents($tmpfname, $data);
        $logo1 = __DIR__ . '/../../../web/bundles/app/img/home_logo.png';
        switch ($nombreReporte) {
            case 'reqxsolicitante':
                $filtro = $session->get('filtro_reportes_soporte_resumen');
                $textoFiltro = array('ubicaciones' => ($filtro['selUbicaciones']) ? $this->getTextoFiltro($em, $filtro['selUbicaciones']) : 'Todos',
                    'edificios' => ($filtro['selEdificios']) ? $this->getTextoFiltro($em, $filtro['selEdificios'], 'Edificio') : 'Todos',
                    'departamentos' => ($filtro['selDepartamento']) ? $this->getTextoFiltro($em, $filtro['selDepartamento'], 'Departamento') : 'Todos',
                    'tiposSoporte' => ($filtro['selTipos']) ? $this->getTextoFiltro($em, $filtro['selTipos'], 'TipoSoporte') : 'Todos',
                    'desde' => $filtro['desde'], 'hasta' => $filtro['hasta']);
                $datos = $this->getDatosResumenReqxSolicitante($em, $filtro, $ubicaciones);
                switch ($tipoSalida) {
                    case 'pdf':
                        $plantilla = 'AppBundle:Reportes:print_resumen_reqxsolicitante.pdf.twig';
                        $arraydata = array('logo' => $logo1, 'grafico' => $tmpfname, 'datos' => $datos, 'filtro' => $textoFiltro);
                        $filename = 'informe_resumenxsector_';
                        break;
                    case 'xls':
                        $plantilla = 'AppBundle:Reportes:export_resumen_reqxsolicitante-xls.html.twig';
                        $arraydata = array('datos' => $datos, 'filtro' => $textoFiltro);
                        $filename = $fileName = 'Reporte_Resumen_RequerimientosxSector_';
                        break;
                }
                break;
            case 'reqxincidencia':
                $filtro = $session->get('filtro_reportes_soporte_resumen');
                $textoFiltro = array('ubicaciones' => ($filtro['selUbicaciones']) ? $this->getTextoFiltro($em, $filtro['selUbicaciones']) : 'Todos',
                    'edificios' => ($filtro['selEdificios']) ? $this->getTextoFiltro($em, $filtro['selEdificios'], 'Edificio') : 'Todos',
                    'departamentos' => ($filtro['selDepartamento']) ? $this->getTextoFiltro($em, $filtro['selDepartamento'], 'Departamento') : 'Todos',
                    'tiposSoporte' => ($filtro['selTipos']) ? $this->getTextoFiltro($em, $filtro['selTipos'], 'TipoSoporte') : 'Todos',
                    'desde' => $filtro['desde'], 'hasta' => $filtro['hasta']);
                $datos = $this->getDatosResumenReqxIncidencia($em, $filtro);
                switch ($tipoSalida) {
                    case 'pdf':
                        $plantilla = 'AppBundle:Reportes:print_resumen_reqxincidencia.pdf.twig';
                        $arraydata = array('logo' => $logo1, 'grafico' => $tmpfname, 'datos' => $datos, 'filtro' => $textoFiltro);
                        $filename = 'informe_resumenxincidencia_';
                        break;
                    case 'xls':
                        $plantilla = 'AppBundle:Reportes:export_resumen_reqxincidencia-xls.html.twig';
                        $arraydata = array('datos' => $datos, 'filtro' => $textoFiltro);
                        $filename = $fileName = 'Reporte_Resumen_RequerimientosxIncidencia_';
                        break;
                }
                break;
            case 'reqanualxestado':
                $filtro = $session->get('filtro_reportes_soporte_anual');
                $textoFiltro = array('ubicaciones' => ($filtro['selUbicaciones']) ? $this->getTextoFiltro($em, $filtro['selUbicaciones']) : 'Todos',
                    'edificios' => ($filtro['selEdificios']) ? $this->getTextoFiltro($em, $filtro['selEdificios'], 'Edificio') : 'Todos',
                    'departamentos' => ($filtro['selDepartamento']) ? $this->getTextoFiltro($em, $filtro['selDepartamento'], 'Departamento') : 'Todos',
                    'tiposSoporte' => ($filtro['selTipos']) ? $this->getTextoFiltro($em, $filtro['selTipos'], 'TipoSoporte') : 'Todos',
                    'anio' => $filtro['anio']);
                $datos = $this->getDatosAnualReqxEstado($em, $filtro);
                switch ($tipoSalida) {
                    case 'pdf':
                        $plantilla = 'AppBundle:Reportes:print_anual_reqxestado.pdf.twig';
                        $arraydata = array('logo' => $logo1, 'grafico' => $tmpfname, 'datos' => $datos, 'filtro' => $textoFiltro);
                        $filename = 'informe_anualxestado_';
                        break;
                    case 'xls':
                        $plantilla = 'AppBundle:Reportes:export_anual_reqxestado-xls.html.twig';
                        $arraydata = array('datos' => $datos, 'filtro' => $textoFiltro);
                        $filename = $fileName = 'Reporte_Anual_RequerimientosxEstado_';
                        break;
                }
                break;
            case 'reqanualxincidencia':
                $filtro = $session->get('filtro_reportes_soporte_anual');
                $textoFiltro = array('ubicaciones' => ($filtro['selUbicaciones']) ? $this->getTextoFiltro($em, $filtro['selUbicaciones']) : 'Todos',
                    'edificios' => ($filtro['selEdificios']) ? $this->getTextoFiltro($em, $filtro['selEdificios'], 'Edificio') : 'Todos',
                    'departamentos' => ($filtro['selDepartamento']) ? $this->getTextoFiltro($em, $filtro['selDepartamento'], 'Departamento') : 'Todos',
                    'tiposSoporte' => ($filtro['selTipos']) ? $this->getTextoFiltro($em, $filtro['selTipos'], 'TipoSoporte') : 'Todos',
                    'anio' => $filtro['anio']);
                $datos = $this->getDatosAnualReqxIncidencia($em, $filtro);
                switch ($tipoSalida) {
                    case 'pdf':
                        $plantilla = 'AppBundle:Reportes:print_anual_reqxincidencia.pdf.twig';
                        $arraydata = array('logo' => $logo1, 'grafico' => $tmpfname, 'datos' => $datos, 'filtro' => $textoFiltro,
                            'meses' => $this->meses, 'color' => self::COLOR);
                        $filename = 'informe_anualxincidencia_';
                        break;
                    case 'xls':
                        $plantilla = 'AppBundle:Reportes:export_anual_reqxincidencia-xls.html.twig';
                        $arraydata = array('datos' => $datos, 'filtro' => $textoFiltro, 'meses' => $this->meses, 'color' => self::COLOR);
                        $filename = $fileName = 'Reporte_Resumen_RequerimientosxIncidencia_';
                        break;
                }
                break;
            case 'eqxincidenciaysector':
                $filtro = $session->get('filtro_reportes_soporte');
                $textoFiltro = array('ubicaciones' => ($filtro['selUbicaciones']) ? $this->getTextoFiltro($em, $filtro['selUbicaciones']) : 'Todos',
                    'edificios' => ($filtro['selEdificios']) ? $this->getTextoFiltro($em, $filtro['selEdificios'], 'Edificio') : 'Todos',
                    'departamentos' => ($filtro['selDepartamento']) ? $this->getTextoFiltro($em, $filtro['selDepartamento'], 'Departamento') : 'Todos',
                    'tiposSoporte' => ($filtro['selTipos']) ? $this->getTextoFiltro($em, $filtro['selTipos'], 'TipoSoporte') : 'Todos',
                    'desde' => $filtro['desde'], 'hasta' => $filtro['hasta']);
                $tiposSoporte = $em->getRepository('ConfigBundle:TipoSoporte')->findAll();
                $tiposEquipos = $em->getRepository('ConfigBundle:Tipo')->findByClase('E');
                // Reporte por Sector, tipo incidencia y tipo de equipos
                $requerimientos = $em->getRepository('AppBundle:Requerimiento')->getRequerimientosParaReporte($filtro, $userId);
                $datos = $this->getDatosDetalladoEqxIncidenciaySector($em, $filtro, $requerimientos, $tiposSoporte, $tiposEquipos);
                switch ($tipoSalida) {
                    case 'pdf':
                        $plantilla = 'AppBundle:Reportes:print_detallado_eqxincidenciaysector.pdf.twig';
                        $arraydata = array('logo' => $logo1, 'datos' => $datos, 'filtro' => $textoFiltro);
                        $filename = 'informe_detalladoxincidencia_';
                        break;
                    case 'xls':
                        $plantilla = 'AppBundle:Reportes:export_detallado_eqxincidenciaysector-xls.html.twig';
                        $arraydata = array('datos' => $datos, 'filtro' => $textoFiltro, 'meses' => $this->meses, 'color' => self::COLOR);
                        $filename = $fileName = 'Reporte_detalladoxincidencia_';
                        break;
                }
                break;
            case 'eqxincidencia':
                $filtro = $session->get('filtro_reportes_soporte');
                $textoFiltro = array('ubicaciones' => ($filtro['selUbicaciones']) ? $this->getTextoFiltro($em, $filtro['selUbicaciones']) : 'Todos',
                    'edificios' => ($filtro['selEdificios']) ? $this->getTextoFiltro($em, $filtro['selEdificios'], 'Edificio') : 'Todos',
                    'departamentos' => ($filtro['selDepartamento']) ? $this->getTextoFiltro($em, $filtro['selDepartamento'], 'Departamento') : 'Todos',
                    'tiposSoporte' => ($filtro['selTipos']) ? $this->getTextoFiltro($em, $filtro['selTipos'], 'TipoSoporte') : 'Todos',
                    'desde' => $filtro['desde'], 'hasta' => $filtro['hasta']);
                $tiposSoporte = $em->getRepository('ConfigBundle:TipoSoporte')->findAll();
                $tiposEquipos = $em->getRepository('ConfigBundle:Tipo')->findByClase('E');
                // Reporte por Sector, tipo incidencia y tipo de equipos
                $requerimientos = $em->getRepository('AppBundle:Requerimiento')->getRequerimientosParaReporte($filtro, $userId);
                $datos = $this->getDatosDetalladoEqxTipoIncidencia($em, $filtro, $requerimientos, $tiposSoporte, $tiposEquipos);
                switch ($tipoSalida) {
                    case 'pdf':
                        $plantilla = 'AppBundle:Reportes:print_detallado_eqxincidencia.pdf.twig';
                        $arraydata = array('logo' => $logo1, 'grafico' => $tmpfname, 'datos' => $datos, 'filtro' => $textoFiltro);
                        $filename = 'informe_detalladoxequipo_';
                        break;
                    case 'xls':
                        $plantilla = 'AppBundle:Reportes:export_detallado_eqxincidencia-xls.html.twig';
                        $arraydata = array('datos' => $datos, 'filtro' => $textoFiltro, 'meses' => $this->meses, 'color' => self::COLOR);
                        $filename = $fileName = 'Reporte_detalladoxequipo_';
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

    /*
     * FUNCIONES PARA OBTENER DATOS
     */

    // REPORTES -> RESUMEN
    private function getDatosResumenReqxSolicitante($em, $filtro, $ubicaciones) {
        $reqxSolicitante = $em->getRepository('AppBundle:Requerimiento')->getResumenPorSolicitante($filtro);
        $finalizado = $asignado = $sinasignar = $totalizado = '';
        $labelreqxSolicitante = array();
        $tablereqxSolicitante = array();
        $total = array('tipo' => 'T', 'nombre' => 'TOTALES', 'id' => null, 'padre' => null, 'finalizado' => 0, 'asignado' => 0, 'sinasignar' => 0);
        foreach ($ubicaciones as $i => $ubic) {
            $totubic = array('tipo' => 'S', 'color' => self::COLOR[$i], 'nombre' => $ubic->getAbreviatura(), 'id' => 'S' . $ubic->getId(), 'padre' => null, 'finalizado' => 0, 'asignado' => 0, 'sinasignar' => 0);
            foreach ($ubic->getEdificios() as $edif) {
                if ($this->getUser()->getEdificios()->contains($edif)) {

                    $totEdif = array('tipo' => 'M', 'nombre' => $edif->getNombre(), 'id' => 'M' . $edif->getId(), 'padre' => 'S' . $ubic->getId(), 'finalizado' => 0, 'asignado' => 0, 'sinasignar' => 0);
                    foreach ($edif->getDepartamentos() as $dep) {
                        foreach ($reqxSolicitante as $item) {
                            if ($item['id'] == $dep->getId()) {
                                $itemfinalizado = intval($item['finalizado']);
                                $itemasignado = intval($item['asignado']);
                                $itemsinasignar = intval($item['sinasignar']);
                                if ($itemfinalizado > 0 || $itemasignado > 0 || $itemsinasignar > 0) {
                                    $totDep = array('tipo' => 'I', 'nombre' => $dep->getNombre(), 'id' => 'I' . $dep->getId(), 'padre' => 'M' . $edif->getId(), 'finalizado' => $itemfinalizado, 'asignado' => $itemasignado, 'sinasignar' => $itemsinasignar);
                                    array_unshift($tablereqxSolicitante, $totDep);
                                    $totEdif['finalizado'] = $totEdif['finalizado'] + $itemfinalizado;
                                    $totEdif['asignado'] = $totEdif['asignado'] + $itemasignado;
                                    $totEdif['sinasignar'] = $totEdif['sinasignar'] + $itemsinasignar;
                                }
                            }
                        }
                    }
                    if ($totEdif['finalizado'] > 0 || $totEdif['asignado'] > 0 || $totEdif['sinasignar'] > 0) {
                        array_unshift($tablereqxSolicitante, $totEdif);
                        $totubic['finalizado'] = $totubic['finalizado'] + $totEdif['finalizado'];
                        $totubic['asignado'] = $totubic['asignado'] + $totEdif['asignado'];
                        $totubic['sinasignar'] = $totubic['sinasignar'] + $totEdif['sinasignar'];
                    }
                }
            }
            if ($totubic['finalizado'] > 0 || $totubic['asignado'] > 0 || $totubic['sinasignar'] > 0) {
                array_unshift($tablereqxSolicitante, $totubic);
                $total['finalizado'] = $total['finalizado'] + $totubic['finalizado'];
                $total['asignado'] = $total['asignado'] + $totubic['asignado'];
                $total['sinasignar'] = $total['sinasignar'] + $totubic['sinasignar'];
                // arma arrays para el grafico
                $labelreqxSolicitante[] = $totubic['nombre'];
                /* $finalizado[] = intval($totubic['finalizado']);
                  $asignado[] = intval($totubic['asignado']);
                  $sinasignar[] = intval($totubic['sinasignar']); */
                $totalizado[] = intval($totubic['finalizado']) + intval($totubic['asignado']) + intval($totubic['sinasignar']);
            }
        }
        array_push($tablereqxSolicitante, $total);
        return array('labels' => $labelreqxSolicitante,
            'data' => $totalizado,
            'table' => $tablereqxSolicitante);
    }

    private function getDatosResumenReqxIncidencia($em, $filtro) {
        $reqxIncidencia = $em->getRepository('AppBundle:Requerimiento')->getResumenPorIncidencia($filtro, $this->getUser()->getId());
        $totalizado = '';
        $labelreqxIncidencia = array();
        $tablereqxIncidencia = array();
        $total = array('tipo' => 'T', 'nombre' => 'TOTALES', 'finalizado' => 0, 'asignado' => 0, 'sinasignar' => 0);

        foreach ($reqxIncidencia as $i => $item) {
            $col = ($i < 20) ? $i : $i - 20;
            $labelreqxIncidencia[] = ($item['abreviatura']) ? $item['abreviatura'] : 'SIN TIPO';
            $tablereqxIncidencia[] = array('tipo' => 'S', 'color' => self::COLOR[$col], 'nombre' => $item['nombre'], 'finalizado' => $item['finalizado'], 'asignado' => $item['asignado'], 'sinasignar' => $item['sinasignar']);
            $total['finalizado'] += $item['finalizado'];
            $total['asignado'] += $item['asignado'];
            $total['sinasignar'] += $item['sinasignar'];
            $totalizado[] = intval($item['finalizado']) + intval($item['asignado']) + intval($item['sinasignar']);
        }
        array_push($tablereqxIncidencia, $total);
        return array('labels' => $labelreqxIncidencia,
            'data' => $totalizado,
            'table' => $tablereqxIncidencia);
    }

    private function getDatosAnualReqxEstado($em, $filtro) {
        $reqGlobal = $em->getRepository('AppBundle:Requerimiento')->getResumenAnualRequerimientoxEstado($filtro, $this->getUser()->getId());
        $labelreqGlobal = array();
        //$finalizado = $asignado = $sinasignar = '';
        $anio = $filtro['anio'];
        $resultado = array();
        $total = array('tipo' => 'T', 'nombre' => 'TOTALES', 'padre' => null, 'finalizado' => 0, 'asignado' => 0, 'sinasignar' => 0);
        $totAnio = array('tipo' => 'S', 'nombre' => $anio, 'padre' => null, 'finalizado' => 0, 'asignado' => 0, 'sinasignar' => 0);
        $pos = 0;
        $ref = date("Y", strtotime(date("Ymd") . "- 1 year"));
        $dataset[] = array('label' => 'Finalizado', 'maxBarThickness' => 30, 'backgroundColor' => self::FILLCOLOR[0], 'borderColor' => self::COLOR[0], 'borderWidth' => 1, 'data' => []);
        $dataset[] = array('label' => 'Asignado', 'maxBarThickness' => 30, 'backgroundColor' => self::FILLCOLOR[1], 'borderColor' => self::COLOR[1], 'borderWidth' => 1, 'data' => []);
        $dataset[] = array('label' => 'Sin Asignar', 'maxBarThickness' => 30, 'backgroundColor' => self::FILLCOLOR[2], 'borderColor' => self::COLOR[2], 'borderWidth' => 1, 'data' => []);
        foreach ($reqGlobal as $i => $item) {
            $label = $this->mesesCorto[intval($item['mes']) - 1] . ' ' . $item['anio'];
            if ($item['anio'] >= $ref) {
                // cuenta para gráfico
                $labelreqGlobal[] = $label;
                $dataset[0]['data'][] = intval($item['finalizado']);
                $dataset[1]['data'][] = intval($item['asignado']);
                $dataset[2]['data'][] = intval($item['sinasignar']);
            }
            if ($anio != $item['anio']) {
                array_unshift($resultado, $totAnio);
                if ($i > 1) {
                    $resultado = UtilsController::arrayMoveElements($resultado, 0, $pos);
                }
                $anio = $item['anio'];
                $totAnio['nombre'] = $anio;
                $totAnio['finalizado'] = $totAnio['asignado'] = $totAnio['sinasignar'] = 0;
                $pos = count($resultado);
            }
            array_push($resultado, array('tipo' => 'I', 'nombre' => $label, 'padre' => $item['anio'], 'finalizado' => intval($item['finalizado']), 'asignado' => intval($item['asignado']), 'sinasignar' => intval($item['sinasignar'])));
            $totAnio['finalizado'] += intval($item['finalizado']);
            $totAnio['asignado'] += intval($item['asignado']);
            $totAnio['sinasignar'] += intval($item['sinasignar']);
            $total['finalizado'] += intval($item['finalizado']);
            $total['asignado'] += intval($item['asignado']);
            $total['sinasignar'] += intval($item['sinasignar']);
        }
        array_unshift($resultado, $totAnio);
        $resultado = UtilsController::arrayMoveElements($resultado, 0, $pos);
        array_push($resultado, $total);
        return array('labelReqGlobal' => $labelreqGlobal, 'seriesReqGlobal' => $dataset, 'tableReqGlobal' => $resultado);
    }

    private function getDatosAnualReqxIncidencia($em, $filtro) {
        $userId = $this->getUser()->getId();
        $dataxTipoIncidencia = $em->getRepository('AppBundle:Requerimiento')->getResumenAnualRequerimientoxEstado($filtro, $userId, 'xTS');
        $totales = $em->getRepository('AppBundle:Requerimiento')->getAnualRequerimientoxIncidencia($filtro, $userId);
        $tipoSoporte = $em->getRepository('AppBundle:Requerimiento')->getResumenAnualTiposSoporte($filtro, $userId);

        /* foreach( $tipoSoporte as $i=> $tipo  ){
          $dataset[$tipo['id']] = array( 'label'=> $tipo['nombre'],'maxBarThickness'=> 30,  'backgroundColor'=> $this->fillcolor[$i],'borderColor'=> $this->color[$i],'borderWidth'=> 1 , 'data'=>[] );
          }
          foreach($totales as $j=>$total){
          $labels[] = $this->mesesCorto[intval($total['mes']) - 1] . ' ' . $total['anio'];
          foreach( $dataxTipoIncidencia as $data){
          $idx = ($data['tipoSoporte']) ? $data['tipoSoporte'] : 0;
          if( $total['aniomes']==$data['aniomes'] ){
          $subtotal = intval($data['finalizado']) + intval($data['asignado']) + intval($data['sinasignar']);
          $dataset[$idx]['data'][$j] = $subtotal;
          }
          if( !isset($dataset[$idx]['data'][$j])) $dataset[$idx]['data'][$j]=0;
          }
          }
          $datasetaux = array();
          foreach( $dataset as $aux){
          if( count($aux['data'])==0 ){ continue; }
          array_push($datasetaux,$aux);
          }
         */
        $dataset = array();
        foreach ($totales as $i => $aux) {
            $col = ($i < 20) ? $i : $i - 20;
            $subtotal = intval($aux['finalizado']) + intval($aux['asignado']) + intval($aux['sinasignar']);
            $nombre = ($aux['nombre']) ? $aux['nombre'] : 'SIN TIPO';
            $dataset[] = array('label' => $nombre, 'maxBarThickness' => 50, 'backgroundColor' => self::FILLCOLOR[$col], 'borderColor' => self::COLOR[$col], 'borderWidth' => 1, 'data' => array($subtotal));
        }

        return array('label' => array('PERÍODO ' . $filtro['anio']), 'data' => $dataset, 'table' => $dataxTipoIncidencia, 'tipos' => $tipoSoporte);
    }

    private function getDatosDetalladoEqxTipoIncidencia($em, $filtro, $requerimientos, $tiposSoporte, $tiposEquipos) {
        // Resumen de tipo de equipo por tipo de incidencia - sector
        $recuento = $i = 0;
        $tabla = $dataset = array();
        $arrTotal = array('tipo' => 'T', 'nombre' => 'TOTALES', 'id' => null, 'padre' => null, 'recuento' => 0, 'FINALIZADO' => 0, 'ASIGNADO' => 0, 'SIN ASIGNAR' => 0);
        foreach ($tiposEquipos as $tipoeq) {
            $arrTipoEq = array('tipo' => 'S', 'nombre' => $tipoeq->getNombre(), 'id' => 'S' . $tipoeq->getId(), 'padre' => NULL, 'recuento' => 0, 'FINALIZADO' => 0, 'ASIGNADO' => 0, 'SIN ASIGNAR' => 0);

            foreach ($tiposSoporte as $tipoSop) {
                if ($filtro['selTipos'] == NULL || in_array($tipoSop->getId(), $filtro['selTipos'])) {
                    $arrTipoSop = array('tipo' => 'M', 'nombre' => $tipoSop->getNombre(), 'id' => 'M' . $tipoeq->getId() . $tipoSop->getId(), 'padre' => 'S' . $tipoeq->getId(), 'recuento' => 0, 'FINALIZADO' => 0, 'ASIGNADO' => 0, 'SIN ASIGNAR' => 0);
                    foreach ($requerimientos as $req) {
                        if ($req->getTipoSoporte()) {
                            if ($req->getTipoSoporte()->getId() == $tipoSop->getId()) {
                                $recuento = $req->getCuentaDeEquipos($tipoeq->getId());
                                $arrTipoSop[$req->getEstado()] += $recuento;
                            }
                        }
                    }
                    if ($arrTipoSop['FINALIZADO'] > 0 || $arrTipoSop['ASIGNADO'] > 0 || $arrTipoSop['SIN ASIGNAR'] > 0) {
                        array_unshift($tabla, $arrTipoSop);
                        $arrTipoEq['FINALIZADO'] += $arrTipoSop['FINALIZADO'];
                        $arrTipoEq['ASIGNADO'] += $arrTipoSop['ASIGNADO'];
                        $arrTipoEq['SIN ASIGNAR'] += $arrTipoSop['SIN ASIGNAR'];
                    }
                }
                else {
                    continue;
                }
            }
            if ($arrTipoEq['FINALIZADO'] > 0 || $arrTipoEq['ASIGNADO'] > 0 || $arrTipoEq['SIN ASIGNAR'] > 0) {
                array_unshift($tabla, $arrTipoEq);
                $subtotal = $arrTipoEq['FINALIZADO'] + $arrTipoEq['ASIGNADO'] + $arrTipoEq['SIN ASIGNAR'];

                $arrTotal['FINALIZADO'] += $arrTipoEq['FINALIZADO'];
                $arrTotal['ASIGNADO'] += $arrTipoEq['ASIGNADO'];
                $arrTotal['SIN ASIGNAR'] += $arrTipoEq['SIN ASIGNAR'];
                $i = ($i < 20) ? ++$i : 0;
                $dataset[] = array('label' => $tipoeq->getNombre(), 'maxBarThickness' => 50, 'backgroundColor' => self::FILLCOLOR[$i], 'borderColor' => self::COLOR[$i], 'borderWidth' => 1, 'data' => array($subtotal));
            }
        }
        array_push($tabla, $arrTotal);
        $total = $arrTotal['FINALIZADO'] + $arrTotal['ASIGNADO'] + $arrTotal['SIN ASIGNAR'];
        $label = array('PERÍODO ' . $filtro['desde'] . ' al ' . $filtro['hasta']);
        return array('tabla' => $tabla, 'total' => $total, 'label' => $label, 'data' => $dataset);
    }

    private function getDatosDetalladoEqxIncidenciaySector($em, $filtro, $requerimientos, $tiposSoporte, $tiposEquipos) {
        $sectores = $em->getRepository('ConfigBundle:Departamento')->getNombreCompleto($this->getUser()->getId());

        // Resumen de tipo de equipo por tipo de incidencia - sector
        $recuento = 0;
        $tablaTipoIncidenciaSector = array();
        $arrTotal = array('tipo' => 'T', 'nombre' => 'TOTALES', 'id' => null, 'padre' => null, 'recuento' => 0, 'FINALIZADO' => 0, 'ASIGNADO' => 0, 'SIN ASIGNAR' => 0);
        foreach ($tiposSoporte as $tipoSop) {
            if ($filtro['selTipos'] == NULL || in_array($tipoSop->getId(), $filtro['selTipos'])) {
                $arrTipoSop = array('tipo' => 'S', 'nombre' => $tipoSop->getNombre(), 'id' => 'S' . $tipoSop->getId(), 'padre' => NULL, 'recuento' => 0, 'FINALIZADO' => 0, 'ASIGNADO' => 0, 'SIN ASIGNAR' => 0);
                foreach ($sectores as $sector) {
                    $arrSector = array('tipo' => 'M', 'nombre' => $sector->getNombreCompleto(), 'id' => 'M' . $tipoSop->getId() . $sector->getId(), 'padre' => 'S' . $tipoSop->getId(), 'recuento' => 0, 'FINALIZADO' => 0, 'ASIGNADO' => 0, 'SIN ASIGNAR' => 0);
                    foreach ($tiposEquipos as $tipoeq) {
                        $arrTipoEq = array('tipo' => 'I', 'nombre' => $tipoeq->getNombre(), 'id' => 'I' . $tipoSop->getId() . $sector->getId() . $tipoeq->getId(), 'padre' => 'M' . $tipoSop->getId() . $sector->getId(), 'recuento' => 0, 'FINALIZADO' => 0, 'ASIGNADO' => 0, 'SIN ASIGNAR' => 0);
                        foreach ($requerimientos as $req) {
                            if ($req->getTipoSoporte()) {
                                if ($req->getSolicitante()->getId() == $sector->getId() &&
                                        $req->getTipoSoporte()->getId() == $tipoSop->getId()) {
                                    $recuento = $req->getCuentaDeEquipos($tipoeq->getId());
                                    $arrTipoEq[$req->getEstado()] += $recuento;
                                }
                            }
                        }
                        if ($arrTipoEq['FINALIZADO'] > 0 || $arrTipoEq['ASIGNADO'] > 0 || $arrTipoEq['SIN ASIGNAR'] > 0) {
                            array_unshift($tablaTipoIncidenciaSector, $arrTipoEq);
                            $arrSector['FINALIZADO'] += $arrTipoEq['FINALIZADO'];
                            $arrSector['ASIGNADO'] += $arrTipoEq['ASIGNADO'];
                            $arrSector['SIN ASIGNAR'] += $arrTipoEq['SIN ASIGNAR'];
                        }
                    }
                    if ($arrSector['FINALIZADO'] > 0 || $arrSector['ASIGNADO'] > 0 || $arrSector['SIN ASIGNAR'] > 0) {
                        array_unshift($tablaTipoIncidenciaSector, $arrSector);
                        $arrTipoSop['FINALIZADO'] += $arrSector['FINALIZADO'];
                        $arrTipoSop['ASIGNADO'] += $arrSector['ASIGNADO'];
                        $arrTipoSop['SIN ASIGNAR'] += $arrSector['SIN ASIGNAR'];
                    }
                }
                if ($arrTipoSop['FINALIZADO'] > 0 || $arrTipoSop['ASIGNADO'] > 0 || $arrTipoSop['SIN ASIGNAR'] > 0) {
                    array_unshift($tablaTipoIncidenciaSector, $arrTipoSop);
                    $arrTotal['FINALIZADO'] += $arrTipoSop['FINALIZADO'];
                    $arrTotal['ASIGNADO'] += $arrTipoSop['ASIGNADO'];
                    $arrTotal['SIN ASIGNAR'] += $arrTipoSop['SIN ASIGNAR'];
                }
            }
            else {
                continue;
            }
        }
        array_push($tablaTipoIncidenciaSector, $arrTotal);
        $totalTipoIncidenciaSector = $arrTotal['FINALIZADO'] + $arrTotal['ASIGNADO'] + $arrTotal['SIN ASIGNAR'];
        return array('tabla' => $tablaTipoIncidenciaSector, 'total' => $totalTipoIncidenciaSector);
    }

    /*
     * FUNCIONES ADICIONALES
     */

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

    /*
     * GRAFICO DE OT POR TECNICO PARA TABLERO
     */

    /**
     * @Route("/getDataOTxTecnico", name="get-data-otxtecnico")
     * @Method("GET")
     * @Template()
     */
    public function getDataOTxTecnico(Request $request) {
        $periodo = UtilsController::ultimoMesParaFiltro($request->get('desde'), $request->get('hasta'), false);
        $em = $this->getDoctrine()->getManager();
        $ots = $em->getRepository('AppBundle:OrdenTrabajo')->getOtsParaReporte($periodo);
        $label = $lblanual = $dtanual = null;
        // REPORTE ENTRE FECHAS DE TAREAS
        $dataset[0] = array('label' => 'Finalizados', 'maxBarThickness' => 50, 'backgroundColor' => self::FILLCOLOR[0],
            'borderColor' => self::COLOR[0], 'borderWidth' => 1, 'data' => array());
        $dataset[1] = array('label' => 'Abiertos', 'maxBarThickness' => 50, 'backgroundColor' => self::FILLCOLOR[1],
            'borderColor' => self::COLOR[1], 'borderWidth' => 1, 'data' => array());
        foreach ($ots as $ot) {
            $label[] = $ot['username'];
            $dataset[0]['data'][] = $ot['cerrado'];
            $dataset[1]['data'][] = $ot['abierto'];
        }
        $xTecnico = array('label' => $label, 'dataset' => $dataset);
        // REPORTE ANUAL POR TECNICO
        //$tecnicos = $em->getRepository('ConfigBundle:Usuario')->findTecnicos();
        $hoy = new \DateTime();
        $ini = date("Ym", strtotime($hoy->format('d-m-Y') . "- 6 month"));

        $dataxmes = $em->getRepository('AppBundle:OrdenTrabajo')->getOtsxMesAnioTecnico($ini);
        $totalxmes = $em->getRepository('AppBundle:OrdenTrabajo')->getOtsxMesAnio($ini);
        $meses = array_keys(array_flip(array_column($dataxmes, 'aniomes')));
        $tecnicos = array_unique(array_column($dataxmes, 'username'));
        $lblanual = array_map(
                function($item) {
            $mes = substr($item, 4, 2) - 1;
            return self::MESCORTO[$mes] . ' ' . substr($item, 0, 4);
        },
                $meses
        );
        $i = 0;
        $totales = array();
        foreach ($tecnicos as $tecnico) {
            $i = ($i < 20) ? ++$i : 0;
            // recorrer periodos
            $data = array();
            foreach ($meses as $mes) {
                $cant = 0;
                foreach ($dataxmes as $dt) {
                    if ($dt['aniomes'] == $mes && $dt['username'] == $tecnico) {
                        $cant = $dt['cantidad'];
                    }
                }
                $data[] = strval($cant);
            }
            $dtanual[] = array('label' => $tecnico, 'data' => $data,
                'maxBarThickness' => 50, 'backgroundColor' => self::FILLCOLOR[$i],
                'borderColor' => self::COLOR[$i], 'borderWidth' => 1);
        }
        $anual = array('label' => $lblanual, 'dataset' => $dtanual, 'totales' => $totalxmes);

        $salida = array('xtecnico' => $xTecnico, 'anual' => $anual);
        if ($request->get('out') == 'A') {
            return $salida;
        }
        else {
            return new JsonResponse($salida);
        }
    }

}