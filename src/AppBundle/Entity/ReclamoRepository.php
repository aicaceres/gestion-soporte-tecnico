<?php
namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;
/**
* ReclamoRepository
*/
class ReclamoRepository extends EntityRepository {

/*
 * Listado de reclamo según criterio
 */        
    public function findByCriteria($data) {
        $query = $this->_em->createQueryBuilder();
        $query->select('r')
                ->from('AppBundle\Entity\Reclamo', 'r')
                ->where("1=1");        
        if($data['estado']!='T'){
            $query->andWhere("r.abierto= '".$data['estado']."'");  
        }              
        if( $data['desde'] || $data['hasta'] ){
             // buscar por fecha            
            if($data['desde']){
                $cadena = " r.fecha >= '".UtilsController::toAnsiDate($data['desde'])." 00:00:00'";
                $query->andWhere($cadena);
              }
            if($data['hasta']){
                $cadena = " r.fecha <= '". UtilsController::toAnsiDate($data['hasta'])." 23:59:59'";
                $query->andWhere($cadena);
            }            
        }
        return $query->getQuery()->getResult();            
    }     

}