<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
* MarcaRepository
*/
class MarcaRepository extends EntityRepository {

    public function findMarcaPorTipo($tipoId) {
        $query = $this->_em->createQueryBuilder();
        $query->select('m')
              ->from('ConfigBundle\Entity\Marca', 'm')
              ->innerJoin('m.equipos', 'e')
              ->innerJoin('e.tipo', 't')  
              ->where("t.id=".$tipoId)
              ->orderBy('m.nombre','ASC')  ;       
        return $query->getQuery()->getResult(); 
    }
   
    
    public function filterMarcaInsumoByTerm($key) {  
        $qb2=$this->_em->createQueryBuilder();
        $ins= $qb2->select(' m2.id ')
            ->from('AppBundle\Entity\Insumo','i')
            ->innerJoin('i.marca', 'm2');
        $query = $this->_em->createQueryBuilder();
            $query->select("m.id,m.nombre text")
                    ->from('ConfigBundle:Marca', 'm')
                    ->where('m.nombre LIKE :key')
                    ->andWhere($query->expr()->in('m.id', $ins->getDQL()))
                    ->orderBy('m.nombre')
                    ->setParameter('key', '%' . $key . '%');
        return $query->getQuery()->getArrayResult();                
    }    
    public function filterMarcaEquipoByTerm($key) {  
        $qb2=$this->_em->createQueryBuilder();
        $ins= $qb2->select(' m2.id ')
            ->from('AppBundle\Entity\Equipo','i')
            ->innerJoin('i.marca', 'm2');
        $query = $this->_em->createQueryBuilder();
            $query->select("m.id,m.nombre text")
                    ->from('ConfigBundle:Marca', 'm')
                    ->where('m.nombre LIKE :key')
                    ->andWhere($query->expr()->in('m.id', $ins->getDQL()))
                    ->orderBy('m.nombre')
                    ->setParameter('key', '%' . $key . '%');
        return $query->getQuery()->getArrayResult();                
    }    
}
