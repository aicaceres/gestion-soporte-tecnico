<?php
namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class VencimientoRepository extends EntityRepository
{
    public function findByCriteria($data){                 
        $query = $this->_em->createQueryBuilder();
        $query->select('v')
              ->from('AppBundle\Entity\Vencimiento', 'v')
              ->where("1=1") ;
        if( $data['tipoId']){
           $query->innerJoin('v.tipo', 't')
                 ->andWhere('t.id='.$data['tipoId']);  
        }
        if( $data['proveedorId']){
           $query->innerJoin('v.proveedor', 'p')
                 ->andWhere('p.id='.$data['proveedorId']);  
        }
        if ($data['estado']) {
            $hoy = new \DateTime();
            $mes = date("Ymd",strtotime($hoy->format('Ymd')."+ 30 days"));             
            switch ($data['estado']) {
                case '1':
                    // en término.. fecha fin mayor a un mes
                    $query->andWhere("v.fechaFin > '".$mes."'");
                    break;
                case '2':
                    // por vencer.. fecha fin igual o menor a un mes
                    $query->andWhere("v.fechaFin <= '".$mes."'");
                    break;
                case '3':
                    // vencido.. fecha fin menor a hoy
                    $query->andWhere("v.fechaFin < '".$hoy->format('Ymd')."'");
                    break;
                default:
                    break;
            }
        }
        if($data['desde']){
          $cadena = " v.fechaInicio >= '".UtilsController::toAnsiDate($data['desde'])."'";
          $query->andWhere($cadena);
        }
        if($data['hasta']){
            $cadena = " v.fechaFin <= '". UtilsController::toAnsiDate($data['hasta'])."'";
            $query->andWhere($cadena);
        }

        return $query->getQuery()->getResult();
    }

    public function findAlertas(){
        $hoy = new \DateTime();
        $mes = date("Ymd",strtotime($hoy->format('Ymd')."+ 30 days")); 
        $query = $this->_em->createQueryBuilder();
        $query->select('v')
              ->from('AppBundle\Entity\Vencimiento', 'v')
              ->where("v.fechaFin <= '".$mes."'")
              ->orderBy('v.fechaFin')  ;
        return $query->getQuery()->getResult();
    }
    
}