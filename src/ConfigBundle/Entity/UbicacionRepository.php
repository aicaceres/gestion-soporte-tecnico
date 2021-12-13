<?php

namespace ConfigBundle\Entity;
use Doctrine\ORM\EntityRepository;

/**
 * UbicacionRepository
 */
class UbicacionRepository extends EntityRepository {

    public function findByUbicacionId($ubicacion_id, $userId = null) {
        $query = $this->_em->createQueryBuilder();
        $query->select('e')
                ->from('ConfigBundle\Entity\Edificio', 'e')
                ->innerJoin('e.usuarios', 'us')
                ->innerJoin('e.ubicacion', 'u')
                ->where("u.id = :ubicacion_id")
                ->distinct()
                ->setParameter('ubicacion_id', $ubicacion_id);
        if ($userId) {
            $query->andWhere('us.id=' . $userId);
        }
        return $query->getQuery()->getArrayResult();
    }

    public function findByEdificioId($edificio_id) {
        $query = $this->getEntityManager()->createQuery("
            SELECT departamento
            FROM ConfigBundle:Departamento departamento
            LEFT JOIN departamento.edificio edificio
            WHERE edificio.id = :edificio_id
        ")->setParameter('edificio_id', $edificio_id);

        return $query->getArrayResult();
    }

    public function getArrayPisos($dpto) {
        $query = $this->getEntityManager()->createQuery("
            SELECT p.id, p.nombre
            FROM ConfigBundle:Departamento d
            LEFT JOIN d.pisos p
            WHERE d.id = :dpto
        ")->setParameter('dpto', $dpto);

        return $query->getArrayResult();
    }

    public function findDptosByUbicacionId($ubic_id) {
        $query = $this->getEntityManager()->createQuery("
            SELECT departamento.id, concat(edificio.nombre,' - ',departamento.nombre) nombre
            FROM ConfigBundle:Departamento departamento
            LEFT JOIN departamento.edificio edificio
            LEFT JOIN edificio.ubicacion ubicacion
            WHERE ubicacion.id = :ubicacion_id
            ORDER BY edificio.nombre, departamento.nombre
        ")->setParameter('ubicacion_id', $ubic_id);

        return $query->getArrayResult();
    }

    public function findDepartamentoByCriteria($filtro) {
        $query = $this->_em->createQueryBuilder();
        $query->select('d')
                ->from('ConfigBundle\Entity\Departamento', 'd')
                ->innerJoin('d.edificio', 'e')
                ->innerJoin('e.ubicacion', 'u')
                ->where("1=1")
                ->orderBy('u.abreviatura, e.nombre, d.nombre');

        if ($filtro['idUbicacion']) {
            $query->andWhere('u.id=' . $filtro['idUbicacion']);
            if ($filtro['idEdificio']) {
                $query->andWhere('e.id=' . $filtro['idEdificio']);
            }
        }
        return $query->getQuery()->getResult();
    }

    public function getNombreCompleto() {
        $query = $this->_em->createQueryBuilder();
        $query->select('d')
                ->from('ConfigBundle\Entity\Departamento', 'd')
                ->innerJoin('d.edificio', 'e')
                ->innerJoin('e.ubicacion', 'u')
                ->where("1=1")
                ->orderBy('u.id, e.id, d.id');
        return $query->getQuery()->getResult();
    }

    public function findEdificiosByUbicaciones($ubic, $salida = null, $user = null) {
        if ($ubic == '')
            $ubic = [];
        $query = $this->_em->createQueryBuilder();
        $query->select('e.id, e.nombre,u.abreviatura')
                ->from('ConfigBundle\Entity\Edificio', 'e')
                ->innerJoin('e.ubicacion', 'u')
                ->innerJoin('e.usuarios', 'us')
                ->where(' u.id IN (:ubicaciones)')
                ->andWhere('us.id=' . $user->getId())
                ->setParameter('ubicaciones', $ubic, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        if ($salida == 'array') {
            return $query->getQuery()->getArrayResult();
        }
        return $query->getQuery()->getResult();
    }

    public function findDepartamentosByEdificios($edif, $salida = null) {
        if ($edif == '')
            $edif = [];
        $query = $this->_em->createQueryBuilder();
        $query->select('d.id,d.nombre,e.nombre edifnombre ,u.abreviatura')
                ->from('ConfigBundle\Entity\Departamento', 'd')
                ->innerJoin('d.edificio', 'e')
                ->innerJoin('e.ubicacion', 'u')
                ->where(' e.id IN (:edificios)')
                ->orderBy('u.abreviatura DESC, e.nombre DESC, d.nombre', 'DESC')
                ->setParameter('edificios', $edif, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        if ($salida == 'array') {
            return $query->getQuery()->getArrayResult();
        }
        return $query->getQuery()->getResult();
    }

    public function findDepartamentosByUbicaciones($ubic) {
        if ($ubic == '')
            $ubic = [];
        $query = $this->_em->createQueryBuilder();
        $query->select('d.id,d.nombre,e.nombre edifnombre ,u.abreviatura')
                ->from('ConfigBundle\Entity\Departamento', 'd')
                ->innerJoin('d.edificio', 'e')
                ->innerJoin('e.ubicacion', 'u')
                ->where(' u.id IN (:ubicaciones)')
                ->orderBy('u.abreviatura DESC, e.nombre DESC, d.nombre', 'DESC')
                ->setParameter('ubicaciones', $ubic, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        return $query->getQuery()->getResult();
    }

    public function findAllOrdenados() {
        $query = $this->_em->createQueryBuilder();
        $query->select('d.id,d.nombre,e.nombre edifnombre ,u.abreviatura')
                ->from('ConfigBundle\Entity\Departamento', 'd')
                ->innerJoin('d.edificio', 'e')
                ->innerJoin('e.ubicacion', 'u')
                ->orderBy('u.abreviatura DESC, e.nombre DESC, d.nombre', 'DESC');
        return $query->getQuery()->getResult();
    }

    public function getUbicacionesParaMonitoreo() {
        $query = $this->_em->createQueryBuilder();
        $query->select('u')
                ->from('ConfigBundle\Entity\Ubicacion', 'u')
                ->innerJoin('u.edificios', 'e')
                ->innerJoin('e.departamentos', 'd')
                ->where('d.ipPrincipal IS NOT NULL')
                ->orWhere('d.ipRespaldo IS NOT NULL');
        return $query->getQuery()->getResult();
    }

    public function getEdificiosParaMonitoreo($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('e')
                ->from('ConfigBundle\Entity\Edificio', 'e')
                ->innerJoin('e.ubicacion', 'u')
                ->innerJoin('e.departamentos', 'd')
                ->where('u.id=' . $id)
                ->andWhere('(d.ipPrincipal IS NOT NULL OR d.ipRespaldo IS NOT NULL)');
        return $query->getQuery()->getResult();
    }

    public function getDepartamentosParaMonitoreo($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('d')
                ->from('ConfigBundle\Entity\Departamento', 'd')
                ->innerJoin('d.edificio', 'e')
                ->where('e.id=' . $id)
                ->andWhere('(d.ipPrincipal IS NOT NULL OR d.ipRespaldo IS NOT NULL)');
        return $query->getQuery()->getResult();
    }

    public function getIpsParaMonitoreo($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('d.ipPrincipal ip')
                ->from('ConfigBundle\Entity\Departamento', 'd')
                ->innerJoin('d.edificio', 'e')
                ->innerJoin('e.ubicacion', 'u')
                ->where('u.id=' . $id)
                ->andWhere('(d.ipPrincipal IS NOT NULL)');
        return $query->getQuery()->getArrayResult();
    }

    public function getUbicacionesPermitidas($userId) {
        $query = $this->_em->createQueryBuilder();
        $query->select('u')
                ->from('ConfigBundle\Entity\Ubicacion', 'u')
                ->innerJoin('u.edificios', 'e')
                ->innerJoin('e.usuarios', 'us')
                ->where('us.id=' . $userId);
        return $query->getQuery()->getResult();
    }

}