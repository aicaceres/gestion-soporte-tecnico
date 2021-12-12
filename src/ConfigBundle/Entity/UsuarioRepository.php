<?php

namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
 * UsuarioRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UsuarioRepository extends EntityRepository {

    public function findTecnicos() {
        $query = $this->_em->createQueryBuilder();
        $query->select('u.id,u.nombre,u.username')
                ->from('ConfigBundle\Entity\Usuario', 'u')
                ->innerJoin('u.rol', 'r')
                ->where("r.tecnico = 1 ")
                ->orderBy('u.nombre');
        return $query->getQuery()->getArrayResult();
    }

    public function findTecnicosHabilitados($edifId) {
        $query = $this->_em->createQueryBuilder();
        $query->select('u.id,u.nombre,u.username')
                ->from('ConfigBundle\Entity\Usuario', 'u')
                ->innerJoin('u.rol', 'r')
                ->innerJoin('u.edificios', 'ed')
                ->where("r.tecnico = 1 ")
                ->andWhere('ed.id = ' . $edifId)
                ->orderBy('u.nombre');
        return $query->getQuery()->getArrayResult();
    }

    public function getTecnicosConOTAbierta() {
        $query = $this->_em->createQueryBuilder();
        $query->select('u')
                ->from('ConfigBundle\Entity\Usuario', 'u')
                ->innerJoin('u.rol', 'r')
                ->innerJoin('u.ordenesTrabajo', 'ot')
                ->where("r.tecnico = 1 ")
                ->andWhere("ot.estado='ABIERTO'")
                ->orderBy('u.nombre');
        return $query->getQuery()->getResult();
    }

}