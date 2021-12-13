<?php

namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

/**
 * EquipoRepository
 */
class EquipoRepository extends EntityRepository {
    /*
     * ajax nombre repetido para compras
     */

    public function findNombreRepetido($nombre, $tipo) {
        $query = $this->_em->createQueryBuilder();
        $query->select('e')
                ->from('AppBundle\Entity\Equipo', 'e')
                ->innerJoin('e.tipo', 't')
                ->where("e.nombre='" . trim($nombre) . "'")
                ->andWhere('t.id=' . $tipo);
        return $query->getQuery()->getOneOrNullResult();
    }

    public function unsetUbicaciones($equipoId) {
        $query = $this->_em->createQueryBuilder();
        $query->update('AppBundle\Entity\EquipoUbicacion', 'u')
                ->set('u.actual', 0)->where('u.equipo = ' . $equipoId);
        return $query->getQuery()->getResult();
    }

    /*
     * Listado de equipos según criteria
     */

    public function findByCriteria($data) {
        $query = $this->_em->createQueryBuilder();
        $query->select('e')
                ->from('AppBundle\Entity\Equipo', 'e')
                ->innerJoin('e.estado', 'es')
                ->where("1=1");

        if ($data['selTipos']) {
            $query->innerJoin('e.tipo', 't')
                    ->andWhere(' t.id IN (:tipos)')
                    ->setParameter('tipos', $data['selTipos'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        }

        if ($data['idMarca']) {
            $query->innerJoin('e.marca', 'ma')
                    ->andWhere('ma.id=' . $data['idMarca']);
            if ($data['idModelo']) {
                $query->innerJoin('e.modelo', 'mo')
                        ->andWhere('mo.id=' . $data['idModelo']);
            }
        }
        if ($data['idEstado'] != 'T') {
            $query->andWhere('es.id=' . $data['idEstado']);
        }
        if ($data['verificado'] != 'T') {
            $query->andWhere('e.verificado=' . $data['verificado']);
        }
        if ($data['idUbicacion']) {
            $query->innerJoin('e.ubicaciones', 'eu')
                    ->innerJoin('eu.departamento', 'd')
                    ->innerJoin('d.edificio', 'ed')
                    ->innerJoin('ed.ubicacion', 'u')
                    ->andWhere('eu.actual=1')
                    ->andWhere('u.id=' . $data['idUbicacion']);
            if ($data['idEdificio']) {
                $query->andWhere('ed.id=' . $data['idEdificio']);
                if ($data['idDepartamento']) {
                    $query->andWhere('d.id=' . $data['idDepartamento']);
                }
            }
        }
        if ($data['txtAdicional']) {
            switch ($data['opAdicional']) {
                case 1:
                    $query->andWhere("e.barcode LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 2:
                    $query->innerJoin('e.proveedor', 'pr')
                            ->andWhere("pr.nombre LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 4:
                    $query->andWhere("e.nroOrdenCompra LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 5:
                    $query->andWhere("e.nroFactura LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 6:
                    $query->andWhere("e.nroRemito LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                default:
                    break;
            }
        }
        if ($data['opAdicional'] == 3) {
            // buscar por fecha de compra
            if ($data['fechaDesde']) {
                $cadena = " e.fechaCompra >= '" . UtilsController::toAnsiDate($data['fechaDesde']) . "'";
                $query->andWhere($cadena);
            }
            if ($data['fechaHasta']) {
                $cadena = " e.fechaCompra <= '" . UtilsController::toAnsiDate($data['fechaHasta']) . "'";
                $query->andWhere($cadena);
            }
        }
        return $query->getQuery()->getResult();
    }

    /*
     * Obtiene los combos filtrados y ordenados
     */

    public function findCombosByCriteria($data, $select, $order, $tabla = '', $userId = null) {
        $query = $this->_em->createQueryBuilder();
        $query->select($select)
                ->from('AppBundle\Entity\Equipo', 'e')
                ->innerJoin('e.tipo', 't')
                ->innerJoin('e.marca', 'ma')
                ->innerJoin('e.modelo', 'mo')
                ->innerJoin('e.estado', 'es')
                ->innerJoin('e.ubicaciones', 'eu')
                ->innerJoin('eu.departamento', 'd')
                ->innerJoin('d.edificio', 'ed')
                ->innerJoin('ed.ubicacion', 'u')
                ->where('eu.actual=1')
                ->orderBy($order);
        if ($userId) {
            $query->innerJoin('ed.usuarios', 'us')
                    ->andWhere('us.id=' . $userId);
        }

        if ($tabla != 'tipo') {
            if ($data['selTipos']) {
                $query->andWhere(' t.id IN (:tipos)')
                        ->setParameter('tipos', $data['selTipos'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
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
        if ($tabla != 'estado') {
            if ($data['idEstado'] != 'T') {
                $query->andWhere('es.id=' . $data['idEstado']);
            }
        }
        if ($data['verificado'] != 'T') {
            $query->andWhere('e.verificado=' . $data['verificado']);
        }
        if ($tabla != 'ubicacion') {
            if ($data['idUbicacion']) {
                $query->andWhere('u.id=' . $data['idUbicacion']);
                if ($tabla != 'edificio') {
                    if ($data['idEdificio']) {
                        $query->andWhere('ed.id=' . $data['idEdificio']);
                        if ($tabla != 'departamento') {
                            if ($data['idDepartamento']) {
                                $query->andWhere('d.id=' . $data['idDepartamento']);
                            }
                        }
                    }
                }
            }
        }
        /* if( $data['txtAdicional'] ){
          switch ($data['opAdicional']) {
          case 1:
          $query->andWhere("e.barcode LIKE '%".trim($data['txtAdicional']) ."%'");
          break;
          case 2:
          $query->innerJoin('e.proveedor', 'pr')
          ->andWhere("pr.nombre LIKE '%".trim($data['txtAdicional']) ."%'");
          break;
          case 3:
          $query->andWhere("e.nroFactura LIKE '%".trim($data['txtAdicional']) ."%'");
          break;
          case 4:
          $query->andWhere("e.nroRemito LIKE '%".trim($data['txtAdicional']) ."%'");
          break;
          default:
          break;
          }
          } */
        return $query->getQuery()->getArrayResult();
    }

    /*
      public function findComboTipo($filtro){
      $query = $this->_em->createQueryBuilder();
      $query->select('DISTINCT t.id,t.nombre')
      ->from('AppBundle\Entity\Equipo', 'e')
      ->innerJoin('e.tipo', 't')
      ->orderBy('t.nombre');
      if ($filtro['idMarca']) {
      $query->innerJoin('e.marca', 'ma')
      ->andWhere('ma.id=' . $filtro['idMarca']);
      if ($filtro['idModelo']) {
      $query->innerJoin('e.modelo', 'mo')
      ->andWhere('mo.id=' . $filtro['idModelo']);
      }
      }
      return $query->getQuery()->getArrayResult();
      }
      public function findComboMarca($filtro){
      $query = $this->_em->createQueryBuilder();
      $query->select('DISTINCT ma.id,ma.nombre')
      ->from('AppBundle\Entity\Equipo', 'e')
      ->innerJoin('e.marca', 'ma')
      ->orderBy('ma.nombre');
      if ($filtro['idTipo']) {
      $query->innerJoin('e.tipo', 't')
      ->andWhere('t.id=' . $filtro['idTipo']);
      }
      return $query->getQuery()->getArrayResult();
      }
      public function findComboModelo($filtro){
      $query = $this->_em->createQueryBuilder();
      $query->select('DISTINCT mo.id,mo.nombre')
      ->from('AppBundle\Entity\Equipo', 'e')
      ->innerJoin('e.modelo', 'mo')
      ->innerJoin('e.marca', 'ma')
      ->where('ma.id=' . $filtro['idMarca'])
      ->orderBy('mo.nombre');
      if ($filtro['idTipo']) {
      $query->innerJoin('e.tipo', 't')
      ->andWhere('t.id=' . $filtro['idTipo']);
      }
      return $query->getQuery()->getArrayResult();
      }
      public function findComboEstado($filtro){
      $query = $this->_em->createQueryBuilder();
      $query->select('DISTINCT es.id,es.nombre')
      ->from('AppBundle\Entity\Equipo', 'e')
      ->innerJoin('e.estado', 'es')
      ->orderBy('es.nombre');
      return $query->getQuery()->getArrayResult();
      }
     */
    /*
     * Resumen de equipos por tipo
     */

    public function findSummaryByCriteria($data, $userId = null) {
        $query = $this->_em->createQueryBuilder();
        $query->select('COUNT(e.id) cantidad, t.nombre')
                ->from('AppBundle\Entity\Equipo', 'e')
                ->innerJoin('e.tipo', 't')
                ->innerJoin('e.marca', 'ma')
                ->innerJoin('e.modelo', 'mo')
                ->innerJoin('e.estado', 'es')
                ->innerJoin('e.ubicaciones', 'eu')
                ->innerJoin('eu.departamento', 'd')
                ->innerJoin('d.edificio', 'ed')
                ->innerJoin('ed.ubicacion', 'u')
                ->where('eu.actual=1')
                ->groupBy('t.nombre')
                ->orderBy('cantidad', 'desc');
        if ($data['selTipos']) {
            $query->andWhere(' t.id IN (:tipos)')
                    ->setParameter('tipos', $data['selTipos'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        }

        if ($data['idMarca']) {
            $query->andWhere('ma.id=' . $data['idMarca']);
            if ($data['idModelo']) {
                $query->andWhere('mo.id=' . $data['idModelo']);
            }
        }
        if ($data['idEstado'] != 'T') {
            $query->andWhere('es.id=' . $data['idEstado']);
        }
        if ($data['verificado'] != 'T') {
            $query->andWhere('e.verificado=' . $data['verificado']);
        }
        if ($data['idUbicacion']) {
            $query->andWhere('u.id=' . $data['idUbicacion']);
            if ($data['idEdificio']) {
                $query->andWhere('ed.id=' . $data['idEdificio']);
                if ($data['idDepartamento']) {
                    $query->andWhere('d.id=' . $data['idDepartamento']);
                }
            }
        }
        if ($data['txtAdicional']) {
            switch ($data['opAdicional']) {
                case 1:
                    $query->andWhere("e.barcode LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 2:
                    $query->innerJoin('e.proveedor', 'pr')
                            ->andWhere("pr.nombre LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 4:
                    $query->andWhere("e.nroOrdenCompra LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 5:
                    $query->andWhere("e.nroFactura LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 6:
                    $query->andWhere("e.nroRemito LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                default:
                    break;
            }
        }
        if ($data['opAdicional'] == 3) {
            // buscar por fecha de compra
            if ($data['fechaDesde']) {
                $cadena = " e.fechaCompra >= '" . UtilsController::toAnsiDate($data['fechaDesde']) . "'";
                $query->andWhere($cadena);
            }
            if ($data['fechaHasta']) {
                $cadena = " e.fechaCompra <= '" . UtilsController::toAnsiDate($data['fechaHasta']) . "'";
                $query->andWhere($cadena);
            }
        }
        if ($userId) {
            $query->innerJoin('ed.usuarios', 'us')
                    ->andWhere('us.id=' . $userId);
        }

        return $query->getQuery()->getArrayResult();
    }

    /*
     * Listado de equipos según criteria
     */

    public function findSqlByCriteria($data, $salida = 'SQL', $searchItem = null, $userId = null) {
        $query = $this->_em->createQueryBuilder();
        if ($salida == 'SQL') {
            $query->select('e.barcode,t.nombre,e.nroSerie,e.nombre, ma.nombre, mo.nombre,es.nombre,u.abreviatura,ed.nombre,d.nombre,p.nombre,e.verificado ');
        }
        else {
            $query->select('e');
        }
        $query->from('AppBundle\Entity\Equipo', 'e')
                ->innerJoin('e.tipo', 't')
                ->leftJoin('e.marca', 'ma')
                ->leftJoin('e.modelo', 'mo')
                ->leftJoin('e.estado', 'es')
                ->leftJoin('e.ubicaciones', 'eu')
                ->leftJoin('eu.piso', 'p')
                ->leftJoin('eu.departamento', 'd')
                ->leftJoin('d.edificio', 'ed')
                ->leftJoin('ed.ubicacion', 'u')
                ->where('eu.actual=1');

        if ($data['selTipos']) {
            $tipos = implode(",", $data['selTipos']);
            if ($salida == 'SQL') {
                $tipos = implode(",", $data['selTipos']);
                $query->andWhere(' t.id IN (' . $tipos . ')');
            }
            else {
                $query->andWhere(' t.id IN (:tipos)')
                        ->setParameter('tipos', $data['selTipos'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
            }
        }
        /* if ($data['idTipo']) {
          $query->andWhere('t.id=' . $data['idTipo']);
          } */
        if ($data['idMarca']) {
            $query->andWhere('ma.id=' . $data['idMarca']);
            if ($data['idModelo']) {
                $query->andWhere('mo.id=' . $data['idModelo']);
            }
        }
        if ($data['idEstado'] != 'T') {
            $query->andWhere('es.id=' . $data['idEstado']);
        }
        if ($data['verificado'] != 'T') {
            $query->andWhere('e.verificado=' . $data['verificado']);
        }
        if ($data['idUbicacion']) {
            $query->andWhere('u.id=' . $data['idUbicacion']);
            if ($data['idEdificio']) {
                $query->andWhere('ed.id=' . $data['idEdificio']);
                if ($data['idDepartamento']) {
                    $query->andWhere('d.id=' . $data['idDepartamento']);
                }
            }
        }
        if ($data['txtAdicional']) {
            switch ($data['opAdicional']) {
                case 1:
                    $query->andWhere("e.barcode LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 2:
                    $query->innerJoin('e.proveedor', 'pr')
                            ->andWhere("pr.nombre LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 4:
                    $query->andWhere("e.nroOrdenCompra LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 5:
                    $query->andWhere("e.nroFactura LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                case 6:
                    $query->andWhere("e.nroRemito LIKE '%" . trim($data['txtAdicional']) . "%'");
                    break;
                default:
                    break;
            }
        }
        if ($data['opAdicional'] == 3) {
            // buscar por fecha de compra
            if ($data['fechaDesde']) {
                $cadena = " e.fechaCompra >= '" . UtilsController::toAnsiDate($data['fechaDesde']) . "'";
                $query->andWhere($cadena);
            }
            if ($data['fechaHasta']) {
                $cadena = " e.fechaCompra <= '" . UtilsController::toAnsiDate($data['fechaHasta']) . "'";
                $query->andWhere($cadena);
            }
        }
        if ($searchItem) {
            $searchQuery = ' e.nombre LIKE \'%' . $searchItem . '%\' OR e.nroSerie LIKE \'%' . $searchItem . '%\' ' .
                    ' OR t.nombre LIKE \'%' . $searchItem . '%\'  OR  ma.nombre LIKE \'%' . $searchItem . '%\' ' .
                    ' OR mo.nombre LIKE \'%' . $searchItem . '%\'  OR  es.nombre LIKE \'%' . $searchItem . '%\' ' .
                    ' OR ( u.abreviatura LIKE \'%' . $searchItem . '%\'  OR ed.nombre LIKE \'%' . $searchItem . '%\' '
                    . ' OR d.nombre LIKE \'%' . $searchItem . '%\' OR p.nombre LIKE \'%' . $searchItem . '%\' )   ';
            $query->andWhere($searchQuery);
        }

        if ($userId) {
            $query->innerJoin('ed.usuarios', 'us')
                    ->andWhere('us.id=' . $userId);
        }

        if ($salida == 'SQL') {
            return $query->getQuery()->getSql();
        }
        else {
            return $query->getQuery()->getResult();
        }
    }

    /*       public function findByCriteriaArray($data) {
      $query = $this->_em->createQueryBuilder();
      $query->select(' e')
      ->from('AppBundle\Entity\Equipo', 'e')
      ->innerJoin('e.estado', 'es')
      ->innerJoin('e.tipo', 't')
      ->innerJoin('e.marca', 'ma')
      ->innerJoin('e.modelo', 'mo')
      ->innerJoin('e.ubicaciones', 'eu')
      ->innerJoin('eu.ubicacion', 'u')
      ->innerJoin('eu.edificio', 'ed')
      ->innerJoin('eu.departamento', 'd')
      ->where("1=1")
      ->andWhere('eu.actual=1');

      if ($data['idTipo']) {
      $query->andWhere('t.id=' . $data['idTipo']);
      }
      if ($data['idMarca']) {
      $query->andWhere('ma.id=' . $data['idMarca']);
      if ($data['idModelo']) {
      $query->andWhere('mo.id=' . $data['idModelo']);
      }
      }
      if ($data['idEstado'] != 'T') {
      $query->andWhere('es.id=' . $data['idEstado']);
      }
      if ($data['verificado'] != 'T') {
      $query->andWhere('e.verificado=' . $data['verificado']);
      }
      if ($data['idUbicacion']) {
      $query->andWhere('u.id=' . $data['idUbicacion']);
      if ($data['idEdificio']) {
      $query->andWhere('ed.id=' . $data['idEdificio']);
      if ($data['idDepartamento']) {
      $query->andWhere('d.id=' . $data['idDepartamento']);
      }
      }
      }
      return $query->getQuery()->getArrayResult();
      } */

    public function findByDepartamento($id) {
        // busca equipos por departamento sin requerimientos u ordenes de trabajo abiertas.
        $qb = $this->_em->createQueryBuilder();
        $qb2 = $this->_em->createQueryBuilder();

        $req = $qb2->select(' eq.id ')
                ->from('AppBundle\Entity\Equipo', 'eq')
                ->innerJoin('eq.requerimientos', 'rd')
                ->innerJoin('rd.requerimiento', 'r')
                ->where("r.estado IN ('SIN ASIGNAR','ASIGNADO')");
        $ord = $qb->select(' eq2.id ')
                ->from('AppBundle\Entity\Equipo', 'eq2')
                ->innerJoin('eq2.ordenesdetrabajo', 'od')
                ->innerJoin('od.ordenTrabajo', 'o')
                ->where("o.estado IN ('ABIERTO')");

        $query = $this->_em->createQueryBuilder();
        $query->select("e.id,concat( t.nombre,'  │  ', e.nombre,'  │  ', e.nroSerie,' │  ',m.nombre,'  │  ',mo.nombre) nombre")
                ->from('AppBundle\Entity\Equipo', 'e')
                ->innerJoin('e.tipo', 't')
                ->innerJoin('e.ubicaciones', 'eu')
                ->innerJoin('eu.departamento', 'd')
                ->innerJoin('e.marca', 'm')
                ->innerJoin('e.modelo', 'mo')
                ->where('eu.actual=1')
                ->andWhere('d.id=' . $id)
                ->andWhere($query->expr()->notIn('e.id', $req->getDQL()))
                ->andWhere($query->expr()->notIn('e.id', $ord->getDQL()))
                ->orderBy("t.nombre, e.nombre, e.nroSerie");

        return $query->getQuery()->getArrayResult();
    }

    public function findByStockTecnico() {
        // busca equipos que esten en stock tecnico.
        $query = $this->_em->createQueryBuilder();
        $query->select("e.id,concat( t.nombre,'  │  ', e.nombre,'  │  ', e.nroSerie,' │  ',m.nombre,'  │  ',mo.nombre) nombre")
                ->from('AppBundle\Entity\Equipo', 'e')
                ->innerJoin('e.tipo', 't')
                ->innerJoin('e.marca', 'm')
                ->innerJoin('e.modelo', 'mo')
                ->innerJoin('e.estado', 'es')
                ->where("es.abreviatura in ('AT','STRAF','STDGR') ")
                ->orderBy("t.nombre, e.nombre, e.nroSerie");

        return $query->getQuery()->getArrayResult();
    }

    public function findEquipobyTerm($q) {
        return false;
    }

    /*
     * PARA SEARCH DATATABLES
     */

    public function count($userId = null) {
        $query = $this->_em->createQueryBuilder();
        $query->select("count(e.id)")
                ->from('AppBundle\Entity\Equipo', 'e');

        if ($userId) {
            $query->innerJoin('e.ubicaciones', 'eu')
                    ->innerJoin('eu.departamento', 'd')
                    ->innerJoin('d.edificio', 'ed')
                    ->innerJoin('ed.usuarios', 'us')
                    ->andWhere('us.id=' . $userId);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    public function getRequiredDTData($start, $length, $orders, $search, $columns, $otherConditions) {
        // Create Main Query
        $query = $this->_em->createQueryBuilder();
        $query->select("e")
                ->from('AppBundle\Entity\Equipo', 'e');

        // Create Count Query
        $countQuery = $this->_em->createQueryBuilder();
        $countQuery->select("count(e.id)")
                ->from('AppBundle\Entity\Equipo', 'e');

        // Create inner joins
        $query
                ->innerJoin('e.tipo', 'tipo')
                ->innerJoin('e.estado', 'estado')
                ->innerJoin('e.marca', 'marca')
                ->innerJoin('e.modelo', 'modelo')
                ->innerJoin('e.ubicaciones', 'eu')
                ->innerJoin('eu.piso', 'p')
                ->innerJoin('eu.departamento', 'd')
                ->innerJoin('d.edificio', 'ed')
                ->innerJoin('ed.ubicacion', 'u');

        $countQuery
                ->innerJoin('e.tipo', 'tipo')
                ->innerJoin('e.estado', 'estado')
                ->innerJoin('e.marca', 'marca')
                ->innerJoin('e.modelo', 'modelo')
                ->innerJoin('e.ubicaciones', 'eu')
                ->innerJoin('eu.piso', 'p')
                ->innerJoin('eu.departamento', 'd')
                ->innerJoin('d.edificio', 'ed')
                ->innerJoin('ed.ubicacion', 'u');

        // Other conditions than the ones sent by the Ajax call ?
        if ($otherConditions === null) {
            // No
            // However, add a "always true" condition to keep an uniform treatment in all cases
            $query->where("eu.actual=1");
            $countQuery->where("eu.actual=1");
        }
        else {
            // Add condition
            $query->where($otherConditions);
            $countQuery->where($otherConditions);
        }

        // Fields Search
        foreach ($columns as $key => $column) {
            if ($column['search']['value'] != '') {
                // $searchItem is what we are looking for
                $searchItem = $column['search']['value'];
                $searchQuery = null;
                // $column['name'] is the name of the column as sent by the JS
                switch ($column['name']) {
                    case 'tipo': {
                            //$searchQuery = 'tipo.nombre LIKE \'%'.$searchItem.'%\'';
                            $searchQuery = 'tipo.nombre LIKE \'' . $searchItem . '\'';
                            break;
                        }
                    case 'nroSerie': {
                            $searchQuery = 'e.nroSerie LIKE \'%' . $searchItem . '%\'';
                            break;
                        }
                    case 'nombre': {
                            $searchQuery = 'e.nombre LIKE \'%' . $searchItem . '%\'';
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
                    case 'estado': {
                            $searchQuery = 'estado.nombre LIKE \'%' . $searchItem . '%\'';
                            break;
                        }
                    case 'ubicacion':
                        $searchQuery = '( u.abreviatura LIKE \'%' . $searchItem . '%\'  OR ed.nombre LIKE \'%' . $searchItem . '%\' '
                                . ' OR d.nombre LIKE \'%' . $searchItem . '%\' OR p.nombre LIKE \'%' . $searchItem . '%\' ) ';
                        break;
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
                    case 'nroSerie': {
                            $orderColumn = 'e.nroSerie';
                            break;
                        }
                    case 'nombre': {
                            $orderColumn = 'e.nombre';
                            break;
                        }
                    case 'tipo': {
                            $orderColumn = 'tipo.nombre';
                            break;
                        }
                    case 'estado': {
                            $orderColumn = 'estado.nombre';
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

    /*
     * PARA LIST DATATABLES
     */

    public function listcount($userId = null) {
        $query = $this->_em->createQueryBuilder();
        $query->select("count(e.id)")
                ->from('AppBundle\Entity\Equipo', 'e');
        if ($userId) {
            $query->innerJoin('e.ubicaciones', 'eu')
                    ->innerJoin('eu.departamento', 'd')
                    ->innerJoin('d.edificio', 'ed')
                    ->innerJoin('ed.usuarios', 'us')
                    ->andWhere('eu.actual=1')
                    ->andWhere('us.id=' . $userId);
        }

        return $query->getQuery()->getSingleScalarResult();
    }

    public function getListDTData($start, $length, $orders, $search, $columns, $otherConditions, $filtro, $userId = null) {
        // Create Main Query
        $query = $this->_em->createQueryBuilder();
        $query->select("e")
                ->from('AppBundle\Entity\Equipo', 'e');

        // Create Count Query
        $countQuery = $this->_em->createQueryBuilder();
        $countQuery->select("count(e.id)")
                ->from('AppBundle\Entity\Equipo', 'e');

        // Create inner joins
        $query
                ->innerJoin('e.tipo', 'tipo')
                ->innerJoin('e.estado', 'estado')
                ->innerJoin('e.marca', 'marca')
                ->innerJoin('e.modelo', 'modelo')
                ->leftJoin('e.ubicaciones', 'eu')
                ->leftJoin('eu.piso', 'p')
                ->leftJoin('eu.departamento', 'd')
                ->leftJoin('d.edificio', 'ed')
                ->leftJoin('ed.ubicacion', 'u');

        $countQuery
                ->innerJoin('e.tipo', 'tipo')
                ->innerJoin('e.estado', 'estado')
                ->innerJoin('e.marca', 'marca')
                ->innerJoin('e.modelo', 'modelo')
                ->leftJoin('e.ubicaciones', 'eu')
                ->leftJoin('eu.piso', 'p')
                ->leftJoin('eu.departamento', 'd')
                ->leftJoin('d.edificio', 'ed')
                ->leftJoin('ed.ubicacion', 'u');

        // Other conditions than the ones sent by the Ajax call ?
        if ($otherConditions === null) {
            // No
            // However, add a "always true" condition to keep an uniform treatment in all cases
            $query->where("eu.actual=1");
            $countQuery->where("eu.actual=1");
        }
        else {
            // Add condition
            $query->where($otherConditions);
            $countQuery->where($otherConditions);
        }

        // Fields Search
        foreach ($filtro as $key => $value) {
            if ($value || $key == 'verificado') {
                // $searchItem is what we are looking for
                $searchItem = $value;
                $searchQuery = null;
                // $column['name'] is the name of the column as sent by the JS
                switch ($key) {
                    case 'selTipos':
                        $searchQuery = 'tipo.id IN (:tipos)';
                        $query->setParameter('tipos', $searchItem, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                        $countQuery->setParameter('tipos', $searchItem, \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                        //  var_dump($searchItem);
                        break;
                    /*  case 'idTipo':
                      {
                      //$searchQuery = 'tipo.nombre LIKE \'%'.$searchItem.'%\'';
                      $searchQuery = 'tipo.id = '.$searchItem;
                      break;
                      } */
                    case 'idMarca': {
                            $searchQuery = 'marca.id= ' . $searchItem;
                            break;
                        }
                    case 'idModelo': {
                            $searchQuery = 'modelo.id=' . $searchItem;
                            break;
                        }
                    case 'idEstado': {
                            if ($searchItem != 'T') {
                                $searchQuery = 'estado.id=' . $searchItem;
                            }
                            break;
                        }
                    case 'idUbicacion': {
                            $searchQuery = 'u.id=' . $searchItem;
                            break;
                        }
                    case 'idEdificio': {
                            $searchQuery = 'ed.id=' . $searchItem;
                            break;
                        }
                    case 'idDepartamento': {
                            $searchQuery = 'd.id=' . $searchItem;
                            break;
                        }
                    case 'verificado': {
                            if ($searchItem != 'T') {
                                $searchQuery = 'e.verificado=' . $searchItem;
                            }
                            break;
                        }
                    case 'opAdicional': {
                            $texto = $filtro['txtAdicional'];
                            switch ($searchItem) {
                                case '1':
                                    $searchQuery = 'e.barcode LIKE \'%' . $texto . '%\'';
                                    break;
                                case '2':
                                    $query->innerJoin('e.proveedor', 'pr');
                                    $countQuery->innerJoin('e.proveedor', 'pr');
                                    $searchQuery = 'pr.nombre LIKE \'%' . trim($texto) . '%\'';
                                    break;
                                case '3':
                                    if ($filtro['fechaDesde']) {
                                        $searchQuery = " e.fechaCompra >= '" . UtilsController::toAnsiDate($filtro['fechaDesde']) . "'";
                                    }
                                    if ($filtro['fechaHasta']) {
                                        if ($searchQuery) {
                                            $searchQuery = $searchQuery . ' AND ';
                                        }
                                        $searchQuery = $searchQuery . " e.fechaCompra <= '" . UtilsController::toAnsiDate($filtro['fechaHasta']) . "'";
                                    }
                                    break;
                                case '4':
                                    $searchQuery = 'e.nroOrdenCompra LIKE \'%' . trim($texto) . '%\'';
                                    break;
                                case '5':
                                    $searchQuery = 'e.nroFactura LIKE \'%' . trim($texto) . '%\'';
                                    break;
                                case '6':
                                    $searchQuery = 'e.nroRemito LIKE \'%' . trim($texto) . '%\'';
                                    break;
                            }
                        }
                }
                if ($searchQuery !== null) {
                    $query->andWhere($searchQuery);
                    $countQuery->andWhere($searchQuery);
                }
            }
        }
        if ($search['value']) {
            $searchItem = trim($search['value']);
            $searchQuery = ' e.nombre LIKE \'%' . $searchItem . '%\' OR e.nroSerie LIKE \'%' . $searchItem . '%\' ' .
                    ' OR tipo.nombre LIKE \'%' . $searchItem . '%\'  OR  marca.nombre LIKE \'%' . $searchItem . '%\' ' .
                    ' OR modelo.nombre LIKE \'%' . $searchItem . '%\'  OR  estado.nombre LIKE \'%' . $searchItem . '%\' ' .
                    ' OR ( u.abreviatura LIKE \'%' . $searchItem . '%\'  OR ed.nombre LIKE \'%' . $searchItem . '%\' '
                    . ' OR d.nombre LIKE \'%' . $searchItem . '%\' OR p.nombre LIKE \'%' . $searchItem . '%\' )   ';
            $query->andWhere($searchQuery);
            $countQuery->andWhere($searchQuery);
        }

        // Limit
        $query->setFirstResult($start)->setMaxResults($length);

        // Order
        foreach ($orders as $key => $order) {
            // $order['name'] is the name of the order column as sent by the JS
            if ($order['name'] != '') {
                $orderColumn = null;

                switch ($order['name']) {
                    case 'nroSerie': {
                            $orderColumn = 'e.nroSerie';
                            break;
                        }
                    case 'nombre': {
                            $orderColumn = 'e.nombre';
                            break;
                        }
                    case 'tipo': {
                            $orderColumn = 'tipo.nombre';
                            break;
                        }
                    case 'estado': {
                            $orderColumn = 'estado.nombre';
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
        if ($userId) {
            $query->innerJoin('ed.usuarios', 'us')
                    ->andWhere('us.id=' . $userId);
            $countQuery->innerJoin('ed.usuarios', 'us')
                    ->andWhere('us.id=' . $userId);
        }

        // Execute
        $results = $query->getQuery()->getResult();
        $countResult = $countQuery->getQuery()->getSingleScalarResult();

        return array(
            "results" => $results,
            "countResult" => $countResult
        );
    }

    public function getEquiposParaMonitoreo($dpto) {
        $query = $this->_em->createQueryBuilder();
        $query->select('e')
                ->from('AppBundle\Entity\Equipo', 'e')
                ->innerJoin('e.ubicaciones', 'u')
                ->innerJoin('u.departamento', 'd')
                ->where("u.actual=1")
                ->andWhere('d.id=' . $dpto)
                ->andWhere('u.redIp is not null');
        return $query->getQuery()->getResult();
    }

}