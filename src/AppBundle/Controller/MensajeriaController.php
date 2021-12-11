<?php
namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use ConfigBundle\Controller\UtilsController;

use AppBundle\Entity\Mensajeria;

/**
 * @Route("/mensajeria")
 */
class MensajeriaController extends Controller
{
     /**
     * @Route("/marcarComoLeido/{id}", name="marcar_como_leido")
     * @Method("GET")
     */        
    public function marcarComoLeidoAction($id)
    {                          
        $em = $this->getDoctrine()->getManager();
        if( $id==0 ){
            // marcar todos
            $mensajes = $em->getRepository('AppBundle:Mensajeria')->findByDestinatario($this->getUser());
            foreach ($mensajes as $mensaje) {
                $mensaje->setFechaLeido(new \DateTime());
                $em->persist($mensaje);
                $em->flush();            
            }
            return new Response( 'TD' );
        }else{
            $mensaje = $em->getRepository('AppBundle:Mensajeria')->find($id);
            $mensaje->setFechaLeido(new \DateTime());
            $em->persist($mensaje);
            $em->flush();      
            return new Response( 'OK' );
        }        
    }

}