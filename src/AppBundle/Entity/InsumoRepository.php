<?php

namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

/**
 * InsumoRepository
 */
class InsumoRepository extends EntityRepository {

    public function findByCriteria($data, $me = false) {
        $query = $this->_em->createQueryBuilder();
        $query->select('i')
            ->from('AppBundle\Entity\Insumo', 'i')
            ->innerJoin('i.tipo', 't')
            ->innerJoin('i.marca', 'ma')
            ->innerJoin('i.modelo', 'mo')
            ->where("t.clase = 'I'");
        if ($me) {
            $query->andWhere("t.subclase = 'INSUMO' ");
        }
        if ($data['idTipo']) {
            $query->andWhere('t.id=' . $data['idTipo']);
        }
        if ($data['idMarca']) {
            $query->andWhere('ma.id=' . $data['idMarca']);
            if ($data['idModelo']) {
                $query->andWhere('mo.id=' . $data['idModelo']);
            }
        }
        return $query->getQuery()->getResult();
    }

    public function combosByCriteria($data, $select, $order, $tabla = '', $me = false) {
        $query = $this->_em->createQueryBuilder();
        $query->select($select)
            ->from('AppBundle\Entity\Insumo', 'i')
            ->innerJoin('i.tipo', 't')
            ->innerJoin('i.marca', 'ma')
            ->innerJoin('i.modelo', 'mo')
            ->where("t.clase = 'I'")
            ->orderBy($order);
        if ($me) {
            $query->andWhere("t.subclase = 'INSUMO' ");
        }
        if ($tabla != 'tipo') {
            if ($data['idTipo']) {
                $query->andWhere('t.id=' . $data['idTipo']);
            }
        }
        if ($tabla != 'marca') {
            if ($data['idMarca']) {
                $query->andWhere('ma.id=' . $data['idMarca']);
                if ($tabla != 'modelo') {
                    if ($data['idModelo']) {
                        $query->andWhere('mo.id=' . $data['idModelo']);
                    }
                }
            }
        }
        return $query->getQuery()->getArrayResult();
    }

    public function findInsumoArray($list) {
        $query = $this->_em->createQueryBuilder();
        $query->select('i.id,i.nombre')
            ->from('AppBundle\Entity\Insumo', 'i')
            ->where("i.id=" . $id);
        return $query->getQuery()->getArrayResult();
    }

    public function filterByTerm($key) {
        $query = $this->_em->createQueryBuilder();
        $query->select("i.id,concat( COALESCE(i.barcode,0) ,' | ',t.nombre,' | ',m.nombre,' | ',mo.nombre ) text")
            ->from('AppBundle\Entity\Insumo', 'i')
            ->innerJoin('i.tipo', 't')
            ->leftJoin('i.marca', 'm')
            ->leftJoin('i.modelo', 'mo')
            ->orderBy('i.barcode,t.nombre,m.nombre,mo.nombre');
        if ($key[0] != '') {
            foreach ($key as $i) {
                $query->orWhere('i.barcode LIKE :key')
                    ->orWhere('t.nombre LIKE :key')
                    ->orWhere('m.nombre LIKE :key')
                    ->orWhere('mo.nombre LIKE :key')
                    ->setParameter('key', '%' . $i . '%');
            }
        }
        return $query->getQuery()->getArrayResult();
    }

    public function findSolicitudes($periodo, $estado = null) {
        $query = $this->_em->createQueryBuilder();
        $query->select('i')
            ->from('AppBundle\Entity\InsumoxTarea', 'i')
            ->innerJoin('i.insumo', 'in')
            ->innerJoin('in.tipo', 't')
            ->where("t.subclase='HARDWARE'");
        switch ($estado) {
            case 1: {
                    $query->andWhere('i.fechaAutorizado is null');
                    break;
                }
            case 2: {
                    $query->andWhere('i.fechaAutorizado is not null');
                    $query->andWhere('i.cantidadAprobada>0');
                    break;
                }
            case 3: {
                    $query->andWhere('i.fechaAutorizado is not null');
                    $query->andWhere('i.cantidadAprobada=0');
                    break;
                }
        }
        if ($periodo) {
            $query->andWhere("i.created>='" . UtilsController::toAnsiDate($periodo['desde']) . " 00:00'")
                ->andWhere("i.created<='" . UtilsController::toAnsiDate($periodo['hasta']) . " 23:59'");
        }
        return $query->getQuery()->getResult();
    }

    public function findSolicitudesPendientes() {
        $query = $this->_em->createQueryBuilder();
        $query->select('count(i.id)')
            ->from('AppBundle\Entity\InsumoxTarea', 'i')
            ->innerJoin('i.insumo', 'in')
            ->innerJoin('in.tipo', 't')
            ->where("i.fechaAutorizado is null")
            ->andWhere("t.subclase='HARDWARE'");
        return $query->getQuery()->getSingleScalarResult();
    }

    public function findEntregasPendientes() {
        $query = $this->_em->createQueryBuilder();
        $query->select('count(i.id)')
            ->from('AppBundle\Entity\InsumoxTarea', 'i')
            ->innerJoin('i.insumo', 'in')
            ->innerJoin('in.tipo', 't')
            ->where("i.fechaAutorizado is null")
            ->andWhere("t.subclase='INSUMO'");
        return $query->getQuery()->getSingleScalarResult();
    }

    public function findInsumoParaRecepcionCompra($det) {
        $query = $this->_em->createQueryBuilder();
        $query->select('i')
            ->from('AppBundle\Entity\Insumo', 'i')
            ->innerJoin('i.tipo', 't')
            ->innerJoin('i.marca', 'm')
            ->innerJoin('i.modelo', 'mo')
            ->where('t.id=' . $det->getTipo()->getId())
            ->andWhere('m.id=' . $det->getItemMarca()->getId())
            ->andWhere('mo.id=' . $det->getItemModelo()->getId());
        return $query->getQuery()->getOneOrNullResult();
    }

    /*
     * PARA DATATABLES
     */

    public function count() {
        $query = $this->_em->createQueryBuilder();
        $query->select("count(e.id)")
            ->from('AppBundle\Entity\Insumo', 'e');
        return $query->getQuery()->getSingleScalarResult();
    }

    public function getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions, $subclase, $deposito) {
        // Create Main Query
        $query = $this->_em->createQueryBuilder();
        $query->select("e")
            ->from('AppBundle\Entity\Insumo', 'e');

        // Create Count Query
        $countQuery = $this->_em->createQueryBuilder();
        $countQuery->select("count(e.id)")
            ->from('AppBundle\Entity\Insumo', 'e');

        // Create inner joins
        $query
            ->innerJoin('e.tipo', 'tipo')
            ->innerJoin('e.marca', 'marca')
            ->innerJoin('e.modelo', 'modelo')
            ->andWhere("tipo.subclase='" . $subclase . "'");

        $countQuery
            ->innerJoin('e.tipo', 'tipo')
            ->innerJoin('e.marca', 'marca')
            ->innerJoin('e.modelo', 'modelo')
            ->andWhere("tipo.subclase='" . $subclase . "'");

        // Other conditions than the ones sent by the Ajax call ?
//        if ($otherConditions === null) {
//            // No
//            // However, add a "always true" condition to keep an uniform treatment in all cases
//            $query->where("1=1");
//            $countQuery->where("1=1");
//        }
//        else {
//            // Add condition
//            $query->where($otherConditions);
//            $countQuery->where($otherConditions);
//        }
        // Fields Search
        foreach ($columns as $key => $column) {

            if ($column['search']['value'] != '') {
                // $searchItem is what we are looking for
                $searchItem = $column['search']['value'];
                $searchQuery = null;
                // $column['name'] is the name of the column as sent by the JS
                switch ($column['name']) {
                    case 'tipo': {
                            $searchQuery = 'tipo.nombre LIKE \'%' . $searchItem . '%\'';
                            break;
                        }
                    case 'barcode': {
                            $searchQuery = 'e.barcode LIKE \'%' . $searchItem . '%\'';
                            break;
                        }
                    case 'marca': {
                            $searchQuery = 'marca.nombre LIKE \'%' . $searchItem . '%\'';
                            break;
                        }
                    case 'modelo': {
                            $searchQuery = 'modelo.nombre LIKE \'%' . $searchItem . '%\'';
                            break;
                        }
                    case 'checks': {
                            if ($searchItem) {
                                // solo los que tienen stock
                                $query->innerJoin('e.stock', 'stk')
                                    ->andWhere('stk.cantidad>0');
                                $countQuery->innerJoin('e.stock', 'stk')
                                    ->andWhere('stk.cantidad>0');
                                if ($deposito) {
                                    $query->innerJoin('stk.deposito', 'dp')
                                        ->andWhere('dp.id = ' . $deposito);
                                    $countQuery->innerJoin('stk.deposito', 'dp')
                                        ->andWhere('dp.id = ' . $deposito);
                                }
                            }
                            break;
                        }
                }

                if ($searchQuery !== null) {
                    $query->andWhere($searchQuery);
                    $countQuery->andWhere($searchQuery);
                }
            }
        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        // Order
        foreach ($orders as $key => $order) {
            // $order['name'] is the name of the order column as sent by the JS
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'barcode': {
                            $orderColumn = 'e.barcode';
                            break;
                        }
                    case 'tipo': {
                            $orderColumn = 'tipo.nombre';
                            break;
                        }
                    case 'marca': {
                            $orderColumn = 'marca.nombre';
                            break;
                        }
                    case 'modelo': {
                            $orderColumn = 'modelo.nombre';
                            break;
                        }
                }

                if ($orderColumn !== null) {
                    $query->orderBy($orderColumn, $order['dir']);
                }
            }
        }

        // Execute
        $results = $query->getQuery()->getResult();
        $countResult = $countQuery->getQuery()->getSingleScalarResult();

        return array(
            "results" => $results,
            "countResult" => $countResult
        );
    }

    public function findEntregaByCriteria($data, $userId) {
        $query = $this->_em->createQueryBuilder();
        $query->select('r')
            ->from('AppBundle\Entity\InsumoEntrega', 'r')
            ->innerJoin('r.solicitante', 's')
            ->innerJoin('s.edificio', 'e')
            ->where("1=1");
        if ($data['estado']) {
            $query->andWhere("r.estado= '" . $data['estado'] . "'");
        }
        if ($data['idUbicacion']) {
            $query->innerJoin('e.ubicacion', 'u')
                ->andWhere('u.id=' . $data['idUbicacion']);
            if ($data['idEdificio']) {
                $query->andWhere('e.id=' . $data['idEdificio']);
                if ($data['idDepartamento']) {
                    $query->andWhere('s.id=' . $data['idDepartamento']);
                }
            }
        }
        if ($data['desde'] || $data['hasta']) {
            // buscar por fecha
            if ($data['desde']) {
                $cadena = " r.fecha >= '" . UtilsController::toAnsiDate($data['desde']) . " 00:00:00'";
                $query->andWhere($cadena);
            }
            if ($data['hasta']) {
                $cadena = " r.fecha <= '" . UtilsController::toAnsiDate($data['hasta']) . " 23:59:59'";
                $query->andWhere($cadena);
            }
        }
        if ($userId) {
            // restringuir segun permiso
            $query->innerJoin('e.usuarios', 'us')
                ->andWhere('us.id=' . $userId);
        }
        return $query->getQuery()->getResult();
    }

    public function findByDeposito($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select("i.id,concat( t.nombre,' | ',m.nombre,' | ',mo.nombre) nombre")
            ->from('AppBundle\Entity\Insumo', 'i')
            ->innerJoin('i.tipo', 't')
            ->innerJoin('i.marca', 'm')
            ->innerJoin('i.modelo', 'mo')
            ->innerJoin('i.stock', 's')
            ->innerJoin('s.deposito', 'd')
            ->where("t.subclase ='INSUMO'")
            ->andWhere('d.id = ' . $id)
            ->andWhere('s.cantidad>0');
        return $query->getQuery()->getArrayResult();
    }

    /**
     * Para informe mesa de entradas
     */
    public function getMovimientosMesaEntrada($filtro) {
        $query = $this->_em->createQueryBuilder();
        $query->select(" ie.fecha, concat(u.abreviatura,' - ',e.nombre) as sector, t.nombre as insumo, d.cantidad ")
            ->from('AppBundle\Entity\InsumoEntrega', 'ie')
            ->innerJoin('ie.detalles', 'd')
            ->innerJoin('d.insumo', 'i')
            ->innerJoin('i.tipo', 't')
            ->innerJoin('ie.solicitante', 's')
            ->innerJoin('s.edificio', 'e')
            ->innerJoin('e.ubicacion', 'u')
            ->where("ie.estado = 'ENTREGADO' ");

        if ($filtro['selTipos']) {
            $query->andWhere(' t.id IN (:tipos)')
                ->setParameter('tipos', $filtro['selTipos'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        }
        if ($filtro['selUbicaciones']) {
            $query->andWhere('u.id IN (:ubicaciones)')
                ->setParameter('ubicaciones', $filtro['selUbicaciones'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
            if ($filtro['selEdificios']) {
                $query->andWhere('e.id IN (:edificios)')
                    ->setParameter('edificios', $filtro['selEdificios'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);

                if ($filtro['selDepartamento']) {
                    $query->andWhere(' s.id IN (:deptos)')
                        ->setParameter('deptos', $filtro['selDepartamento'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                }
            }
        }
        if ($filtro['desde']) {
            $cadena = " ie.fecha >= '" . UtilsController::toAnsiDate($filtro['desde']) . "'";
            $query->andWhere($cadena);
        }
        if ($filtro['hasta']) {
            $cadena = " ie.fecha <= '" . UtilsController::toAnsiDate($filtro['hasta']) . "'";
            $query->andWhere($cadena);
        }

        return $query->getQuery()->getArrayResult();
    }

}