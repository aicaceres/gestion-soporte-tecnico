<?php

namespace AppBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use ConfigBundle\Controller\UtilsController;

/**
 * @Route("/entrega")
 */
class InsumoEntregaController extends Controller {

    /**
     * @Route("/", name="insumo_entrega")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request) {
        UtilsController::haveAccess($this->getUser(), 'insumo_entrega');
        $em = $this->getDoctrine()->getManager();
        if ($this->getUser()->getRol()->getAdmin()) {
            $em->getFilters()->disable('softdeleteable');
        }
        return $this->render('AppBundle:InsumoEntrega:index.html.twig');
    }

}