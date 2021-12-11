<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
* ModeloRepository
*/
class ModeloRepository extends EntityRepository {

    public function findByMarcaId($marcaId) {
        $query = $this->getEntityManager()->createQuery("
        SELECT mo
        FROM ConfigBundle:Modelo mo
        LEFT JOIN mo.marca ma
        WHERE ma.id = :marcaId
        ")->setParameter('marcaId', $marcaId);

        return $query->getArrayResult();
    }
    
    public function filterModeloInsumoByTerm($key,$marca=null) {  
        $qb2=$this->_em->createQueryBuilder();
        $ins= $qb2->select(' m2.id ')
            ->from('AppBundle\Entity\Insumo','i')
            ->innerJoin('i.modelo', 'm2');
        $query = $this->_em->createQueryBuilder();
            $query->select("m.id,m.nombre text")
                    ->from('ConfigBundle:Modelo', 'm')
                    ->where('m.nombre LIKE :key')
                    ->andWhere($query->expr()->in('m.id', $ins->getDQL()))
                    ->orderBy('m.nombre')
                    ->setParameter('key', '%' . $key . '%');
        if($marca){
            $query->innerJoin('m.marca', 'ma')
                  ->andWhere('ma.id='.$marca);  
        }                
        return $query->getQuery()->getArrayResult();                
    }        
    
    public function filterModeloEquipoByTerm($key,$marca=null) {  
        $qb2=$this->_em->createQueryBuilder();
        $ins= $qb2->select(' m2.id ')
            ->from('AppBundle\Entity\Equipo','i')
            ->innerJoin('i.modelo', 'm2');
        $query = $this->_em->createQueryBuilder();
            $query->select("m.id,m.nombre text")
                    ->from('ConfigBundle:Modelo', 'm')
                    ->where('m.nombre LIKE :key')
                    ->andWhere($query->expr()->in('m.id', $ins->getDQL()))
                    ->orderBy('m.nombre')
                    ->setParameter('key', '%' . $key . '%');
        if($marca){
            $query->innerJoin('m.marca', 'ma')
                  ->andWhere('ma.id='.$marca);  
        }                
        return $query->getQuery()->getArrayResult();                
    }        
    
    public function findModelosByMarcas($marcas,$salida=null){
        if( $marcas=='' ) $marcas = [];
        $query = $this->_em->createQueryBuilder();
        $query->select('mo.id, mo.nombre modelo ,m.nombre marca')
                ->from('ConfigBundle\Entity\Modelo', 'mo')
                ->innerJoin('mo.marca', 'm')
                ->where(' m.id IN (:marcas)')
                  ->setParameter('marcas', $marcas, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);          
        if($salida=='array'){
            return $query->getQuery()->getArrayResult();  
        }
        return $query->getQuery()->getResult();  
    }
    
}
