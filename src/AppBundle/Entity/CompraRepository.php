<?php

namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

class CompraRepository extends EntityRepository {

    public function findProductoByCodigo($codigo) {
        //Buscar en insumos
        $query = $this->_em->createQueryBuilder();
        $query->select('i')
                ->from('AppBundle\Entity\Insumo', 'i')
                ->where("i.codigo='" . $codigo . "'")
                ->orWhere("i.barcode='" . $codigo . "'");
        $insumo = $query->getQuery()->getOneOrNullResult();
        if ($insumo) {
            $data = array('id' => $insumo->getId(), 'txt' => $insumo->getTexto());
            $res = array('tipo' => 'I', 'res' => $data);
        }
        else {
            //chequear que no exista en equipo
            $query = $this->_em->createQueryBuilder();
            $query->select('e')
                    ->from('AppBundle\Entity\Equipo', 'e')
                    ->where("e.codigo='" . $codigo . "'")
                    ->orWhere("e.barcode='" . $codigo . "'");
            $equipo = $query->getQuery()->getOneOrNullResult();
            if ($equipo) {
                $data = array('id' => $equipo->getId(), 'txt' => $equipo->getTexto());
                $res = array('tipo' => 'E', 'res' => $data);
            }
            else {
                $res = array('tipo' => 'N', 'res' => NULL);
            }
        }
        return $res;
    }

    /*
     * Compras para listado
     */

    public function findByCriteria($filtro, $searchItem = null) {
        $query = $this->_em->createQueryBuilder();
        $query->select('c')
                ->from('AppBundle\Entity\Compra', 'c')
                ->innerJoin('c.proveedor', 'p')
                ->innerJoin('c.razonSocial', 'r')
                ->leftJoin('c.solicitante', 's')
                ->where('1=1');

        if ($filtro['cuenta']) {
            $query->andWhere("c.nroCuenta LIKE '%" . $filtro['cuenta'] . "%'");
        }
        if ($filtro['estado']) {
            $query->andWhere("c.estado='" . $filtro['estado'] . "'");
        }
        if ($filtro['proveedorId']) {
            $query->andWhere('p.id=' . $filtro['proveedorId']);
        }
        if ($filtro['razonSocialId']) {
            $query->andWhere('r.id=' . $filtro['razonSocialId']);
        }
        if ($filtro['solicitanteId']) {
            $query->andWhere('s.id=' . $filtro['solicitanteId']);
        }
        if ($filtro['desde']) {
            $cadena = " c.fechaCompra >= '" . UtilsController::toAnsiDate($filtro['desde']) . "'";
            $query->andWhere($cadena);
        }
        if ($filtro['hasta']) {
            $cadena = " c.fechaCompra <= '" . UtilsController::toAnsiDate($filtro['hasta']) . "'";
            $query->andWhere($cadena);
        }
        if ($searchItem) {
            $searchQuery = ' c.ordenCompra LIKE \'%' . $searchItem . '%\' OR c.nroRemito LIKE \'%' . $searchItem . '%\' ' .
                    ' OR c.nroFactura LIKE \'%' . $searchItem . '%\'  OR  c.estado LIKE \'%' . $searchItem . '%\' ' .
                    ' OR p.nombre LIKE \'%' . $searchItem . '%\'  OR  r.abreviatura LIKE \'%' . $searchItem . '%\' ';
            $query->andWhere($searchQuery);
        }
        return $query->getQuery()->getResult();
    }

    /*   public function findFacturasByPeriodo($desde,$hasta){
      $query = $this->_em->createQueryBuilder();
      $query->select('f')
      ->from('ComprasBundle\Entity\Factura', 'f')
      ->where("f.estado!='ANULADO'")
      ->andWhere("f.fechaFactura>='".UtilsController::toAnsiDate($desde)." 00:00'")
      ->andWhere("f.fechaFactura<='".UtilsController::toAnsiDate($hasta)." 23:59'");
      return $query->getQuery()->getResult();
      }

      public function findNotaDCByPeriodo($desde,$hasta){
      $query = $this->_em->createQueryBuilder();
      $query->select('c')
      ->from('ComprasBundle\Entity\NotaDebCred', 'c')
      ->where("c.estado!='ANULADO'")
      ->andWhere("c.fecha>='".UtilsController::toAnsiDate($desde)." 00:00'")
      ->andWhere("c.fecha<='".UtilsController::toAnsiDate($hasta)." 23:59'");
      return $query->getQuery()->getResult();
      }

      public function findByProveedorId($id) {
      $query = $this->getEntityManager()->createQuery("
      SELECT f.id,f.tipoFactura, f.prefijoNro, f.facturaNro, f.total, f.nroComprobante
      FROM ComprasBundle:Factura f
      LEFT JOIN f.proveedor p
      WHERE p.id = :id
      ORDER BY f.fechaFactura DESC
      ")->setParameter('id', $id);

      return $query->getArrayResult();
      }

      public function findByCriteria($unidneg, $provId=NULL, $desde=NULL, $hasta=NULL){
      $query = $this->_em->createQueryBuilder();
      $query->select('p')
      ->from('ComprasBundle\Entity\Factura', 'p')
      ->innerJoin('p.unidadNegocio', 'u')
      ->where("u.id=".$unidneg) ;
      if($provId){
      $query->innerJoin('p.proveedor', 'pr')
      ->andWhere('pr.id='.$provId);
      }
      if($desde){
      $cadena = " p.fechaFactura >= '".UtilsController::toAnsiDate($desde)."'";
      $query->andWhere($cadena);
      }
      if($hasta){
      $cadena = " p.fechaFactura <= '". UtilsController::toAnsiDate($hasta)."'";
      $query->andWhere($cadena);
      }
      return $query->getQuery()->getResult();
      } */

    /**
     * Lista de Equipos para informe de compas - bienes en stock
     */
    public function getBienesEnStock($filtro) {
        $query = $this->_em->createQueryBuilder();
        $query->select('e')
                ->from('AppBundle\Entity\Equipo', 'e')
                ->innerJoin('e.detcompra', 'rcd')
                ->innerJoin('rcd.compraDetalle', 'cd')
                ->innerJoin('cd.compra', 'c')
                ->innerJoin('c.razonSocial', 'rs')
                ->where('1 = 1');
        if ($filtro['estado'] == 'STS') {
            // solo se filtra por el estado del equipo si se buscan STS
            $query->innerJoin('e.estado', 'es')
                    ->andWhere("es.abreviatura='" . $filtro['estado'] . "'");
        }
        if ($filtro['cuenta']) {
            $query->andWhere("c.nroCuenta LIKE '%" . $filtro['cuenta'] . "%'");
        }
        if ($filtro['equipoId']) {
            $query->innerJoin('e.tipo', 't')
                    ->andWhere("t.id=" . $filtro['equipoId']);
        }
        if ($filtro['desde']) {
            $cadena = " c.fechaCompra >= '" . UtilsController::toAnsiDate($filtro['desde']) . "'";
            $query->andWhere($cadena);
        }
        if ($filtro['hasta']) {
            $cadena = " c.fechaCompra <= '" . UtilsController::toAnsiDate($filtro['hasta']) . "'";
            $query->andWhere($cadena);
        }
        if ($filtro['proveedorId']) {
            $query->innerJoin('e.proveedor', 'p')
                    ->andWhere('p.id = ' . $filtro['proveedorId']);
        }
        if ($filtro['razonSocialId']) {
            $query->andWhere('rs.id = ' . $filtro['razonSocialId']);
        }
        else {
            $query->andWhere('rs.razonSocial=1');
        }
        return $query->getQuery()->getResult();
    }

}