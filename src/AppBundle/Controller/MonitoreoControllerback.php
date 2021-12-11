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

class MonitoreoController extends Controller
{
    /**
     * @Route("/monitoreo", name="monitoreo_estadored")
     * @Method("GET")
     * @Template("AppBundle:Monitoreo:index.html.twig")
     */
    public function estadoAction(Request $request)
    {
        UtilsController::haveAccess($this->getUser(),'monitoreo_estadored');
        $em = $this->getDoctrine()->getManager();
        $session = $this->get('session');
        $sessionFiltro = $session->get('filtro_monitoreo');           
        switch ($request->get('_opFiltro')) {
            case 'buscar':
                $filtro = array(
                    'selUbicaciones' => $request->get('selUbicaciones')
                );
                break;
            default:
                //desde paginacion, se usa session
                if ($sessionFiltro) {
                    $filtro = array(
                        'selUbicaciones' => $sessionFiltro['selUbicaciones']
                    );
                } else {
                    $filtro = array('selUbicaciones'=>[]);
                }
                break;
        }
        $session->set('filtro_monitoreo', $filtro);
        $entities = $em->getRepository('ConfigBundle:Ubicacion')->getUbicacionesParaMonitoreo();
        $ubicaciones = array();       
        $reclamosAbiertos = 0;       
        if($filtro['selUbicaciones']){        
            foreach ($entities as $ent){             
                if( in_array($ent->getId(), $filtro['selUbicaciones']) ){                            
                    $estado = ['danger'=>0,'warning'=>0,'success'=>0];            
                    foreach( $ent->getEdificios() as $edif ){
                        foreach ($edif->getDepartamentos() as $dep){
                            if( $dep->getIpPrincipal() ){                        
                                $estado[UtilsController::checkIP( $dep->getIpPrincipal(),1 )['estado']] += 1 ;   
                            }
                            if( $dep->getIpRespaldo() ){                        
                                $estado[UtilsController::checkIP( $dep->getIpRespaldo(),1 )['estado']] += 1 ;                           
                            }
                            if( $dep->getProveedor() ){
                                $reclamosAbiertos += $dep->getProveedor()->getReclamosAbiertos(); 
                            }
                        }
                    }                       
                    $ubicaciones[] = array('id'=>$ent->getId(), 'nombre'=>$ent->getAbreviatura(),'estado'=>$estado);
                }    
            }        
        }
        return array(
            'ubicaciones' => $ubicaciones,    
            'ubiclist' => $entities,
            'reclamos' => $reclamosAbiertos                     
        );
    }   
    
    /**
     * @Route("/monitoreoIpDepartamento/{id}", name="monitoreo_ip_departamento")      
     * @Method("GET")     
     */    
    public function monitoreoIpDepartamentoAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $dep = $em->getRepository('ConfigBundle:Departamento')->find($id);
        $ip1 = UtilsController::checkIP( $dep->getIpPrincipal(),1 );
        $ip2 = UtilsController::checkIP( $dep->getIpRespaldo(),1 );  
        $salida1 = ( ($dep->getIpPrincipal())? $ip1['text'] : '' );
        $salida2 = ( ($dep->getIpRespaldo())? $ip2['text'] : '' );
        return new JsonResponse( $salida1.  chr(13) . chr(13) . $salida2 );        
    }
    
    /**
     * @Route("/testIP", name="monitoreo_testip")      
     * @Method("GET")     
     */    
    public function testIPAction(Request $request)
    {
        $ip = $request->get('ip');          
        $n = ($request->get('intentos')) ? $request->get('intentos') : 1 ;          
        $resp = UtilsController::checkIP( $ip, ($n)?$n:1 ); 
        $salidaPing = str_replace(chr(161),"í" , $resp['salidaPing']);
        $salidaPing = str_replace(chr(160),"á" , $salidaPing);              
        $partial = $this->renderView('AppBundle:Monitoreo:partial-testip.html.twig', 
                array('salidaPing'=> $salidaPing, 'intentos'=>$n, 'respuesta'=>$resp ));
        return new Response( $partial );        
    }
    
    /**
     * @Route("/getMonitoreoEdificios/{ubicid}", name="get_monitoreo_edificios")      
     * @Method("GET")     
     */    
    public function getMonitoreoEdificiosAction($ubicid)
    {
        $em = $this->getDoctrine()->getManager();        
        $entities = $em->getRepository('ConfigBundle:Ubicacion')->getEdificiosParaMonitoreo($ubicid);
        $html = '';
        foreach ($entities as $ent){ 
            $estado = ['danger'=>0,'warning'=>0,'success'=>0];            
                foreach ($ent->getDepartamentos() as $dep){
                    if( $dep->getIpPrincipal() ){                        
                        $estado[UtilsController::checkIP( $dep->getIpPrincipal(),1 )['estado']] += 1 ;   
                    }
                    if( $dep->getIpRespaldo() ){                        
                        $estado[UtilsController::checkIP( $dep->getIpRespaldo(),1 )['estado']] += 1 ;                           
                    }
                }
            $html = $html. $this->renderView('AppBundle:Monitoreo:partial-edificio.html.twig', 
                array('data' => array('ubicacion'=>$ubicid, 'id'=>$ent->getId(),'nombre'=>$ent->getNombre(),'estado'=>$estado) )
            );                                
        }
        return new Response( $html );       
    }    
    /**
     * @Route("/getMonitoreoDepartamentos/{edifid}", name="get_monitoreo_departamentos")      
     * @Method("GET")     
     */    
    public function getMonitoreoDepartamentosAction($edifid) {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('ConfigBundle:Ubicacion')->getDepartamentosParaMonitoreo($edifid);
        $html = '';   
        $bg = 'bg-success';
        foreach ($entities as $dep) {            
            if ($dep->getIpPrincipal()) {
                $estado['principal'] = UtilsController::checkIP($dep->getIpPrincipal(), 1);
            }
            if ($dep->getIpRespaldo()) {
                $estado['respaldo'] = UtilsController::checkIP($dep->getIpRespaldo(), 1);
            }else{
                $estado['respaldo']['estado'] = $estado['principal']['estado'];
            }
            if( $estado['principal']['estado']=='danger' && $estado['respaldo']['estado']=='danger' ){
                $bg = 'bg-danger';
            }elseif ($estado['principal']['estado']=='success' && $estado['respaldo']['estado']=='success') {
                $bg = 'bg-success';
            }else{
                $bg = 'bg-warning';
            }
            $html = $html . $this->renderView('AppBundle:Monitoreo:partial-departamento.html.twig',
                            array('departamento' => $dep, 'monitoreo' => $estado, 'bg'=>$bg )
            );
        }
        return new Response($html);
    }
    
    /**
     * @Route("/getMonitoreoEquipos/{dptoid}", name="get_monitoreo_equipos")      
     * @Method("GET")     
     */    
    public function getMonitoreoEquiposAction($dptoid) {
        $em = $this->getDoctrine()->getManager();
        $equipos = $em->getRepository('AppBundle:Equipo')->getEquiposParaMonitoreo($dptoid);
        $departamento = $em->getRepository('ConfigBundle:Departamento')->find($dptoid);
        $padre = 'U'.$departamento->getEdificio()->getUbicacion()->getId().'E'.$departamento->getEdificio()->getId().'D'.$departamento->getId();
        $html = '';
        foreach( $equipos as $equipo ){
            $ip = $equipo->getUbicacionActual()->getRedIp();
            $estado = UtilsController::checkIP($ip, 1);            
            $html = $html . $this->renderView('AppBundle:Monitoreo:partial-equipo.html.twig',
                            array('equipo' => $equipo, 'monitoreo' => $estado,'ip'=> $ip, 'padre' => $padre )
            );
        }
        return new Response($html);
    }
    
 
}