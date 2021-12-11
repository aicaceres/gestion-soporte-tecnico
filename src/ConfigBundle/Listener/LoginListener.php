<?php
namespace ConfigBundle\Listener;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Router;
use Symfony\Component\Security\Core\SecurityContext;

class LoginListener
{
    private $contexto, $router, $session, $id, $user = null;
    
    public function __construct(SecurityContext $context, Router $router, Session $session)
    {
        $this->contexto = $context;
        $this->router = $router;
        $this->session = $session;
    }
    
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        $token = $event->getAuthenticationToken();
        $this->id = $token->getUser();
        $this->id = $token->getUser()->getId();
        $this->user = $token->getUser();
    }
    
    public function onKernelResponse(FilterResponseEvent $event) {        
        if (null != $this->id) {
            if($this->user->getActivo()){
                if($this->contexto->isGranted('ROLE_ADMIN')) {
                    $this->session->getFlashBag()->add('info','Usuario administrador');
                    $dashboard = $this->router->generate( $this->user->getInitRoute() );
                } else if($this->contexto->isGranted('ROLE_USER')) {
                    $this->session->getFlashBag()->add('info','Usuario administrativo interno');
                    $dashboard = $this->router->generate($this->user->getInitRoute());
                } else {
                    $this->session->getFlashBag()->add('info','Usuario operador externo');
                    $dashboard = $this->router->generate($this->user->getInitRoute());
                }
            }else{
                $this->session->getFlashBag()->add('danger','Su usuario no se encuentra activo.');
                $dashboard = $this->router->generate('usuario_logout');
            }
 
            $event->setResponse(new RedirectResponse($dashboard));
            $event->stopPropagation();
        }
    }    

}