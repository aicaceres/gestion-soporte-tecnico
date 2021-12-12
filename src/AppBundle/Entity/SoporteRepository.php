<?php

namespace AppBundle\Entity;
use Doctrine\ORM\EntityRepository;
use ConfigBundle\Controller\UtilsController;

/**
 * SoporteRepository
 */
class SoporteRepository extends EntityRepository {
    /*
     * Listado de orden de trabajo según criterio
     */

    public function findOTByCriteria($data, $user) {
        $query = $this->_em->createQueryBuilder();
        $query->select('o')
                ->from('AppBundle\Entity\OrdenTrabajo', 'o')
                ->innerJoin('o.requerimiento', 'r')
                ->innerJoin('r.solicitante', 's')
                ->innerJoin('s.edificio', 'e')
                ->where("1=1");

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

        if ($data['idTecnico']) {
            $query->innerJoin('o.tecnico', 't')
                    ->andWhere('t.id=' . $data['idTecnico']);
        }
        if ($data['idTipoIncidencia']) {
            $query->innerJoin('r.tipoSoporte', 'i')
                    ->andWhere('i.id=' . $data['idTipoIncidencia']);
        }
        if ($data['estado']) {
            $query->andWhere("o.estado= '" . $data['estado'] . "'");
        }
        if ($data['desde'] || $data['hasta']) {
            // buscar por fecha
            if ($data['desde']) {
                $cadena = " o.fechaOrden >= '" . UtilsController::toAnsiDate($data['desde']) . " 00:00:00'";
                $query->andWhere($cadena);
            }
            if ($data['hasta']) {
                $cadena = " o.fechaOrden <= '" . UtilsController::toAnsiDate($data['hasta']) . " 23:59:59'";
                $query->andWhere($cadena);
            }
        }
        if (!$user->getRol()->getAdmin()) {
            // restringuir segun permiso
            $query->innerJoin('e.usuarios', 'us')
                    ->andWhere('us.id=' . $user->getId());
        }
        return $query->getQuery()->getResult();
    }

    /*
     * Listado de requerimientos según criterio
     */

    public function findByCriteria($data, $user) {
        $query = $this->_em->createQueryBuilder();
        $query->select('r')
                ->from('AppBundle\Entity\Requerimiento', 'r')
                ->innerJoin('r.solicitante', 's')
                ->innerJoin('s.edificio', 'e')
                ->where("1=1");
        if ($data['idTipo']) {
            $query->innerJoin('r.tipoSoporte', 'ts')
                    ->andWhere("ts.id= " . $data['idTipo']);
        }
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
                $cadena = " r.fechaRequerimiento >= '" . UtilsController::toAnsiDate($data['desde']) . " 00:00:00'";
                $query->andWhere($cadena);
            }
            if ($data['hasta']) {
                $cadena = " r.fechaRequerimiento <= '" . UtilsController::toAnsiDate($data['hasta']) . " 23:59:59'";
                $query->andWhere($cadena);
            }
        }
        if (!$user->getRol()->getAdmin()) {
            // restringuir segun permiso
            $query->innerJoin('e.usuarios', 'us')
                    ->andWhere('us.id=' . $user->getId());
        }
        return $query->getQuery()->getResult();
    }

    public function getReqyOts($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('r')
                ->from('AppBundle\Entity\Requerimiento', 'r')
                ->innerJoin('r.ordentrabajoAsociadas', 'o')
                ->innerJoin('o.detalles', 'od')
                ->innerJoin('od.equipo', 'e')
                ->where("e.id=" . $id)
                ->orderBy('r.fechaRequerimiento');
        return $query->getQuery()->getResult();
    }

    public function findTareasReemplazoEquipoPdf($id) {
        $query = $this->_em->createQueryBuilder();
        $query->select('t')
                ->from('AppBundle:Tarea', 't')
                ->innerJoin('t.tipoTarea', 'tt')
                ->innerJoin('t.ordenTrabajo', 'o')
                ->where('o.id=' . $id)
                ->andWhere("tt.abreviatura='CE'");
        return $query->getQuery()->getResult();
    }

    public function getResumenAnualRequerimientoxEstado($filtro, $tipo = null) {
        $query = $this->_em->createQueryBuilder();
        if ($tipo == 'xTS') {
            $query->select("t.id tipoSoporte,DATE_FORMAT(r.fechaRequerimiento, '%Y%m') aniomes, DATE_FORMAT(r.fechaRequerimiento, '%m') mes, DATE_FORMAT(r.fechaRequerimiento, '%Y') anio, "
                            . " SUM(CASE WHEN r.estado='FINALIZADO' THEN 1 ELSE 0 END) AS finalizado, "
                            . " SUM(CASE WHEN r.estado='SIN ASIGNAR' THEN 1 ELSE 0 END) AS sinasignar, "
                            . " SUM(CASE WHEN r.estado='ASIGNADO' THEN 1 ELSE 0 END) AS asignado")
                    ->from('AppBundle:Requerimiento', 'r')
                    ->leftJoin('r.tipoSoporte', 't')
                    ->where("r.estado!='CANCELADO'")
                    ->groupBy('t.id,aniomes')
                    //->having('(finalizado>0 or asignado>0 or sinasignar>0)')
                    ->orderBy('t.id,aniomes', 'ASC');
        }
        else {
            $query->select("DATE_FORMAT(r.fechaRequerimiento, '%Y%m') aniomes, DATE_FORMAT(r.fechaRequerimiento, '%m') mes, DATE_FORMAT(r.fechaRequerimiento, '%Y') anio, "
                            . " SUM(CASE WHEN r.estado='FINALIZADO' THEN 1 ELSE 0 END) AS finalizado, "
                            . " SUM(CASE WHEN r.estado='SIN ASIGNAR' THEN 1 ELSE 0 END) AS sinasignar, "
                            . " SUM(CASE WHEN r.estado='ASIGNADO' THEN 1 ELSE 0 END) AS asignado")
                    ->from('AppBundle:Requerimiento', 'r')
                    ->leftJoin('r.tipoSoporte', 't')
                    ->where("r.estado!='CANCELADO'")
                    ->groupBy('aniomes')
                    ->orderBy('aniomes', 'ASC');
        }
        if ($filtro['selTipos']) {
            $query->andWhere(' t.id IN (:tipos)')
                    ->setParameter('tipos', $filtro['selTipos'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        }
        if ($filtro['selUbicaciones']) {
            $query->innerJoin('r.solicitante', 's')
                    ->innerJoin('s.edificio', 'e')
                    ->innerJoin('e.ubicacion', 'u')
                    ->andWhere('u.id IN (:ubicaciones)')
                    ->setParameter('ubicaciones', $filtro['selUbicaciones'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
            if ($filtro['selEdificios']) {
                $query->andWhere('e.id IN (:edificios)')
                        ->setParameter('edificios', $filtro['selEdificios'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                ;
                if ($filtro['selDepartamento']) {
                    $query->andWhere(' s.id IN (:deptos)')
                            ->setParameter('deptos', $filtro['selDepartamento'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                }
            }
        }
        if ($filtro['anio']) {
            // buscar por anio
            $cadena = " DATE_FORMAT(r.fechaRequerimiento, '%Y') = '" . $filtro['anio'] . "'";
            $query->andWhere($cadena);
        }
        return $query->getQuery()->getArrayResult();
    }

    public function getResumenAnualTiposSoporte($filtro) {
        $query = $this->_em->createQueryBuilder();
        $query->select(" distinct (CASE WHEN t.id is NULL THEN 0 ELSE t.id END) id"
                        . ",(CASE WHEN t.id is NULL THEN 'SIN TIPO' ELSE t.nombre END) nombre ")
                ->from('AppBundle:Requerimiento', 'r')
                ->leftJoin('r.tipoSoporte', 't')
                ->where("r.estado!='CANCELADO'")
                ->orderBy('t.id');
        if ($filtro['selTipos']) {
            $query->andWhere(' t.id IN (:tipos)')
                    ->setParameter('tipos', $filtro['selTipos'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        }
        if ($filtro['selUbicaciones']) {
            $query->innerJoin('r.solicitante', 's')
                    ->innerJoin('s.edificio', 'e')
                    ->innerJoin('e.ubicacion', 'u')
                    ->andWhere('u.id IN (:ubicaciones)')
                    ->setParameter('ubicaciones', $filtro['selUbicaciones'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
            if ($filtro['selEdificios']) {
                $query->andWhere('e.id IN (:edificios)')
                        ->setParameter('edificios', $filtro['selEdificios'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                ;
                if ($filtro['selDepartamento']) {
                    $query->andWhere(' s.id IN (:deptos)')
                            ->setParameter('deptos', $filtro['selDepartamento'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                }
            }
        }
        if ($filtro['anio']) {
            // buscar por anio
            $cadena = " DATE_FORMAT(r.fechaRequerimiento, '%Y') = '" . $filtro['anio'] . "'";
            $query->andWhere($cadena);
        }
        return $query->getQuery()->getArrayResult();
    }

    public function getAnualRequerimientoxIncidencia($filtro) {
        $query = $this->_em->createQueryBuilder();
        $query->select("t.id id,t.nombre nombre,"
                        . " SUM(CASE WHEN r.estado='FINALIZADO' THEN 1 ELSE 0 END) AS finalizado, "
                        . " SUM(CASE WHEN r.estado='SIN ASIGNAR' THEN 1 ELSE 0 END) AS sinasignar, "
                        . " SUM(CASE WHEN r.estado='ASIGNADO' THEN 1 ELSE 0 END) AS asignado")
                ->from('AppBundle:Requerimiento', 'r')
                ->leftJoin('r.tipoSoporte', 't')
                ->where("r.estado!='CANCELADO'")
                ->groupBy('t.id,t.nombre')
                ->orderBy('t.id', 'ASC');
        if ($filtro['selTipos']) {
            $query->andWhere(' t.id IN (:tipos)')
                    ->setParameter('tipos', $filtro['selTipos'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        }
        if ($filtro['selUbicaciones']) {
            $query->innerJoin('r.solicitante', 's')
                    ->innerJoin('s.edificio', 'e')
                    ->innerJoin('e.ubicacion', 'u')
                    ->andWhere('u.id IN (:ubicaciones)')
                    ->setParameter('ubicaciones', $filtro['selUbicaciones'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
            if ($filtro['selEdificios']) {
                $query->andWhere('e.id IN (:edificios)')
                        ->setParameter('edificios', $filtro['selEdificios'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                ;
                if ($filtro['selDepartamento']) {
                    $query->andWhere(' s.id IN (:deptos)')
                            ->setParameter('deptos', $filtro['selDepartamento'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                }
            }
        }
        if ($filtro['anio']) {
            // buscar por anio
            $cadena = " DATE_FORMAT(r.fechaRequerimiento, '%Y') = '" . $filtro['anio'] . "'";
            $query->andWhere($cadena);
        }
        return $query->getQuery()->getArrayResult();
    }

    public function getRequerimientosParaReporte($filtro) {
        $query = $this->_em->createQueryBuilder();
        $query->select("r")
                ->from('AppBundle:Requerimiento', 'r')
                ->where("r.estado!='CANCELADO'");
        if ($filtro['selTipos']) {
            $query->leftJoin('r.tipoSoporte', 't')
                    ->andWhere(' t.id IN (:tipos)')
                    ->setParameter('tipos', $filtro['selTipos'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
        }
        if ($filtro['selUbicaciones']) {
            $query->innerJoin('r.solicitante', 's')
                    ->innerJoin('s.edificio', 'e')
                    ->innerJoin('e.ubicacion', 'u')
                    ->andWhere('u.id IN (:ubicaciones)')
                    ->setParameter('ubicaciones', $filtro['selUbicaciones'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
            if ($filtro['selEdificios']) {
                $query->andWhere('e.id IN (:edificios)')
                        ->setParameter('edificios', $filtro['selEdificios'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                ;
                if ($filtro['selDepartamento']) {
                    $query->andWhere(' s.id IN (:deptos)')
                            ->setParameter('deptos', $filtro['selDepartamento'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                }
            }
        }
        if ($filtro['desde'] || $filtro['hasta']) {
            // buscar por fecha
            if ($filtro['desde']) {
                $cadena = " r.fechaRequerimiento >= '" . UtilsController::toAnsiDate($filtro['desde']) . " 00:00:00'";
                $query->andWhere($cadena);
            }
            if ($filtro['hasta']) {
                $cadena = " r.fechaRequerimiento <= '" . UtilsController::toAnsiDate($filtro['hasta']) . " 23:59:59'";
                $query->andWhere($cadena);
            }
        }
        return $query->getQuery()->getResult();
    }

    public function getResumenPorSolicitante($filtro) {
        $query = $this->_em->createQueryBuilder();
        $query->select(" s.id ,s.nombre, e.id edificio, u.id ubicacion,"
                        . " SUM(CASE WHEN r.estado='FINALIZADO' THEN 1 ELSE 0 END) AS finalizado, "
                        . " SUM(CASE WHEN r.estado='SIN ASIGNAR' THEN 1 ELSE 0 END) AS sinasignar, "
                        . " SUM(CASE WHEN r.estado='ASIGNADO' THEN 1 ELSE 0 END) AS asignado")
                ->from('AppBundle:Requerimiento', 'r')
                ->innerJoin('r.solicitante', 's')
                ->innerJoin('s.edificio', 'e')
                ->innerJoin('e.ubicacion', 'u')
                ->leftJoin('r.tipoSoporte', 't')
                ->where("r.estado!='CANCELADO'")
                ->groupBy('s.id ,s.nombre, e.id, u.id');
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
                ;
                if ($filtro['selDepartamento']) {
                    $query->andWhere(' s.id IN (:deptos)')
                            ->setParameter('deptos', $filtro['selDepartamento'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                }
            }
        }
        /*  if ($filtro['idUbicacion']) {
          $query->andWhere('u.id=' . $filtro['idUbicacion']);
          if ($filtro['idEdificio']) {
          $query->andWhere('e.id=' . $filtro['idEdificio']);
          if ($filtro['idDepartamento']) {
          $query->andWhere('s.id=' . $filtro['idDepartamento']);
          }
          }
          } */
        if ($filtro['desde'] || $filtro['hasta']) {
            // buscar por fecha
            if ($filtro['desde']) {
                $cadena = " r.fechaRequerimiento >= '" . UtilsController::toAnsiDate($filtro['desde']) . " 00:00:00'";
                $query->andWhere($cadena);
            }
            if ($filtro['hasta']) {
                $cadena = " r.fechaRequerimiento <= '" . UtilsController::toAnsiDate($filtro['hasta']) . " 23:59:59'";
                $query->andWhere($cadena);
            }
        }
        return $query->getQuery()->getArrayResult();
    }

    public function getResumenPorIncidencia($filtro) {
        $query = $this->_em->createQueryBuilder();
        $query->select(" t.id, t.nombre,t.abreviatura,"
                        . " SUM(CASE WHEN r.estado='FINALIZADO' THEN 1 ELSE 0 END) AS finalizado, "
                        . " SUM(CASE WHEN r.estado='SIN ASIGNAR' THEN 1 ELSE 0 END) AS sinasignar, "
                        . " SUM(CASE WHEN r.estado='ASIGNADO' THEN 1 ELSE 0 END) AS asignado")
                ->from('AppBundle:Requerimiento', 'r')
                ->innerJoin('r.solicitante', 's')
                ->innerJoin('s.edificio', 'e')
                ->innerJoin('e.ubicacion', 'u')
                ->leftJoin('r.tipoSoporte', 't')
                ->where("r.estado!='CANCELADO'")
                ->groupBy('t.id ,t.nombre');
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
                ;
                if ($filtro['selDepartamento']) {
                    $query->andWhere(' s.id IN (:deptos)')
                            ->setParameter('deptos', $filtro['selDepartamento'], \Doctrine\DBAL\Connection::PARAM_STR_ARRAY);
                }
            }
        }
        if ($filtro['desde'] || $filtro['hasta']) {
            // buscar por fecha
            if ($filtro['desde']) {
                $cadena = " r.fechaRequerimiento >= '" . UtilsController::toAnsiDate($filtro['desde']) . " 00:00:00'";
                $query->andWhere($cadena);
            }
            if ($filtro['hasta']) {
                $cadena = " r.fechaRequerimiento <= '" . UtilsController::toAnsiDate($filtro['hasta']) . " 23:59:59'";
                $query->andWhere($cadena);
            }
        }
        return $query->getQuery()->getArrayResult();
    }

    public function getOtsParaReporte($periodo) {
        $query = $this->_em->createQueryBuilder();
        $query->select(" t.id, t.username, "
                        . " SUM(CASE WHEN ot.estado='ABIERTO' THEN 1 ELSE 0 END) AS abierto, "
                        . " SUM(CASE WHEN ot.estado='CERRADO' THEN 1 ELSE 0 END) AS cerrado")
                ->from('AppBundle:OrdenTrabajo', 'ot')
                ->innerJoin('ot.tecnico', 't')
                ->where("ot.estado!='CANCELADO'")
                ->groupBy('t.id ,t.username');
        if ($periodo['desde'] || $periodo['hasta']) {
            // buscar por fecha
            if ($periodo['desde']) {
                $cadena = " ot.fechaOrden >= '" . UtilsController::toAnsiDate($periodo['desde']) . " 00:00:00'";
                $query->andWhere($cadena);
            }
            if ($periodo['hasta']) {
                $cadena = " ot.fechaOrden <= '" . UtilsController::toAnsiDate($periodo['hasta']) . " 23:59:59'";
                $query->andWhere($cadena);
            }
        }
        return $query->getQuery()->getArrayResult();
    }

    public function getOtsxMesAnioTecnico($ini) {
        $query = $this->_em->createQueryBuilder();
        $query->select("DATE_FORMAT(r.fechaOrden, '%Y%m') aniomes, t.username, SUM(1) AS cantidad")
                ->from('AppBundle:OrdenTrabajo', 'r')
                ->innerJoin('r.tecnico', 't')
                ->where("r.estado!='CANCELADO'")
                ->andWhere("DATE_FORMAT(r.fechaOrden, '%Y%m') >" . $ini)
                ->groupBy('aniomes,t.username')
                ->orderBy('aniomes,t.username', 'ASC');
        return $query->getQuery()->getArrayResult();
    }

    public function getOtsxMesAnio($ini) {
        $query = $this->_em->createQueryBuilder();
        $query->select("DATE_FORMAT(r.fechaOrden, '%Y%m') aniomes, SUM(1) AS cantidad")
                ->from('AppBundle:OrdenTrabajo', 'r')
                ->where("r.estado!='CANCELADO'")
                ->andWhere("DATE_FORMAT(r.fechaOrden, '%Y%m') >" . $ini)
                ->groupBy('aniomes')
                ->orderBy('aniomes', 'ASC');
        return $query->getQuery()->getArrayResult();
    }

}