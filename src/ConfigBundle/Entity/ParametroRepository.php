<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

class ParametroRepository extends EntityRepository {

    public function getInicial($tabla) {
        $query = $this->_em->createQueryBuilder();
        $query->select('t')
              ->from($tabla, 't')  
              ->where("t.inicial=1") ;       
        return $query->getQuery()->getOneOrNullResult();  
    }
        
    public function filterByTerm($clase,$key) {      
        $query = $this->_em->createQueryBuilder();
            $query->select("t.id,t.nombre text")
                    ->from('ConfigBundle:Tipo', 't')
                    ->where('t.nombre LIKE :key')
                    ->andWhere('t.clase = :clase')
                    ->orderBy('t.nombre')
                    ->setParameter('key', '%' . $key . '%')
                    ->setParameter('clase', $clase);
        return $query->getQuery()->getArrayResult();                
    }
    
    public function filterTipoInsumoByTerm($key) {  
        $qb2=$this->_em->createQueryBuilder();
        $ins= $qb2->select(' t2.id ')
            ->from('AppBundle\Entity\Insumo','i')
            ->innerJoin('i.tipo', 't2');
        $query = $this->_em->createQueryBuilder();
            $query->select("t.id,t.nombre text")
                    ->from('ConfigBundle:Tipo', 't')
                    ->where('t.nombre LIKE :key')
                    ->andWhere($query->expr()->in('t.id', $ins->getDQL()))
                    ->orderBy('t.nombre')
                    ->setParameter('key', '%' . $key . '%');
        return $query->getQuery()->getArrayResult();                
    }
    public function filterTipoEquipoByTerm($key) {  
        $qb2=$this->_em->createQueryBuilder();
        $ins= $qb2->select(' t2.id ')
            ->from('AppBundle\Entity\Equipo','i')
            ->innerJoin('i.tipo', 't2');
        $query = $this->_em->createQueryBuilder();
            $query->select("t.id,t.nombre text")
                    ->from('ConfigBundle:Tipo', 't')
                    ->where('t.nombre LIKE :key')
                    ->andWhere($query->expr()->in('t.id', $ins->getDQL()))
                    ->orderBy('t.nombre')
                    ->setParameter('key', '%' . $key . '%');
        return $query->getQuery()->getArrayResult();                
    }
    
    public function filterEstadoEquipoByTerm($key) {  
        $qb2=$this->_em->createQueryBuilder();
        $ins= $qb2->select(' m2.id ')
            ->from('AppBundle\Entity\Equipo','i')
            ->innerJoin('i.estado', 'm2');
        $query = $this->_em->createQueryBuilder();
            $query->select("m.id,m.nombre text")
                    ->from('ConfigBundle:Estado', 'm')
                    ->where('m.nombre LIKE :key')
                    ->andWhere($query->expr()->in('m.id', $ins->getDQL()))
                    ->orderBy('m.nombre')
                    ->setParameter('key', '%' . $key . '%');
        return $query->getQuery()->getArrayResult();                
    }        
    
}
