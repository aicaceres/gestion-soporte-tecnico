<?php

namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use ConfigBundle\Controller\UtilsController;

/**
 * AppBundle\Entity\OrdenTrabajo
 * @ORM\Table(name="orden_trabajo")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SoporteRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\Loggable()
 */
class OrdenTrabajo {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var datetime $fechaOrden
     * @ORM\Column(name="fecha_orden", type="datetime", nullable=false)
     * @Gedmo\Versioned()
     */
    private $fechaOrden;

    /**
     * @var string $nroOrden
     * @ORM\Column(name="nro_orden", type="string",length=20, nullable=true)
     * @Gedmo\Versioned()
     */
    protected $nroOrden;

    /**
     * @var string $jira
     * @ORM\Column(name="jira", type="string",length=20, nullable=true)
     * @Gedmo\Versioned()
     */
    protected $jira;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario",inversedBy="ordenesTrabajo" )
     * @ORM\JoinColumn(name="tecnico_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $tecnico;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Requerimiento",inversedBy="ordentrabajoAsociadas" )
     * @ORM\JoinColumn(name="requerimiento_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $requerimiento;

    /**
     * @var text $descripcion
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $descripcion;

    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     * @Gedmo\Versioned()
     */
    /* ABIERTO - CERRADO - CANCELADO */
    protected $estado = 'ABIERTO';

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrdenTrabajoDetalle", mappedBy="ordenTrabajo",cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $detalles;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Tarea", mappedBy="ordenTrabajo")
     */
    protected $tareas;

    /**
     * @ORM\Column(name="alta_prioridad", type="boolean", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $altaPrioridad = false;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Documentacion", mappedBy="ordenTrabajo",cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $documentos;

    /**
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var datetime $updated
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * @var User $updatedBy
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;

    /**
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;
    private $tareasReubicacionEquipo;
    private $tareasReemplazoEquipo;

    /**
     * Constructor
     */
    public function __construct() {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tareasReubicacionEquipo = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tareasReemplazoEquipo = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getTareasReubicacionEquipo() {
        foreach ($this->getTareas() as $tarea) {
            if ($tarea->getTipoTarea()->getAbreviatura() == 'RE')
                $this->tareasReubicacionEquipo[] = $tarea;
        }
        return $this->tareasReubicacionEquipo;
    }

    public function getTareasReemplazoEquipo() {
        foreach ($this->getTareas() as $tarea) {
            if ($tarea->getTipoTarea()->getAbreviatura() == 'CE')
                $this->tareasReemplazoEquipo[] = $tarea;
        }
        return $this->tareasReemplazoEquipo;
    }

    public function getTiempoEnSegundos() {
        $inicio = $this->getFechaOrden();
        $hoy = new \DateTime();
        $fin = ( $this->getEstado() == 'ABIERTO') ? $hoy : $this->getUpdated();
        $diff = strtotime($fin->format('YmdHis')) - strtotime($inicio->format('YmdHis'));
        return ( $this->getEstado() == 'CANCELADO') ? 0 : $diff;
    }

    public function getTiempo() {
        $inicio = $this->getFechaOrden();
        $hoy = new \DateTime();
        $fin = ( $this->getEstado() == 'ABIERTO') ? $hoy : $this->getUpdated();
        $intervalo = $inicio->diff($fin);
        $diff = strtotime($fin->format('YmdHis')) - strtotime($inicio->format('YmdHis'));
        $salida = round($diff / 84600) . ( (round($diff / 84600) == 1) ? ' día' : ' días' );
        if (round($diff / 84600) == 0) {
            $salida = round($diff / 3600) . ' Hs';
            if (round($diff / 3600) == 0) {
                $salida = round($diff / 60) . ' Min';
            }
        }
        $salida = ( $this->getEstado() == 'CANCELADO') ? '' : $salida;

        $seg = ($intervalo->format('%s') > 50) ? 1 : 0;
        $minutos = $intervalo->format('%i') + $seg;
        if ($minutos > 50) {
            $min = 1;
            $minutos = 0;
        }
        else {
            $min = 0;
        }
        $horas = ( ($intervalo->format('%H') + $min) > 0) ? ($intervalo->format('%H') + $min) . 'h ' : '';
        $dias = ($intervalo->format('%d') > 0) ? $intervalo->format('%dd ') : '';
        $meses = ($intervalo->format('%m') > 0) ? $intervalo->format('%mM ') : '';
        $anios = ($intervalo->format('%Y') > 0) ? $intervalo->format('%Y A ') : '';
        $tiempo = $anios . $meses . $dias . $horas . (($minutos > 0) ? $minutos . 'm ' : '');
        return ( $this->getEstado() == 'CANCELADO') ? '' : $tiempo;
        //$tiempo = $intervalo->format('%Y %m %d %H %i %s');
    }

    public function __toString() {
        return strval($this->getId());
    }

    public function getNroOT() {
        return str_pad($this->getRequerimiento()->getId(), 6, '0', STR_PAD_LEFT) . '/' . str_pad($this->getNroOrden(), 2, '0', STR_PAD_LEFT);
    }

    public function DescripcionCorta() {
        return UtilsController::myTruncate($this->getDescripcion(), 100);
    }

    public function getCantDetallesSinEntregar() {
        $cant = 0;
        foreach ($this->getDetalles() as $det) {
            if (!$det->getEntregado()) {
                $cant = $cant + 1;
            }
        }
        return $cant;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set fechaOrden
     *
     * @param \DateTime $fechaOrden
     * @return OrdenTrabajo
     */
    public function setFechaOrden($fechaOrden) {
        $this->fechaOrden = $fechaOrden;

        return $this;
    }

    /**
     * Get fechaOrden
     *
     * @return \DateTime
     */
    public function getFechaOrden() {
        return $this->fechaOrden;
    }

    /**
     * Set nroOrden
     *
     * @param string $nroOrden
     * @return OrdenTrabajo
     */
    public function setNroOrden($nroOrden) {
        $this->nroOrden = $nroOrden;

        return $this;
    }

    /**
     * Get nroOrden
     *
     * @return string
     */
    public function getNroOrden() {
        return $this->nroOrden;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return OrdenTrabajo
     */
    public function setDescripcion($descripcion) {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string
     */
    public function getDescripcion() {
        return $this->descripcion;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return OrdenTrabajo
     */
    public function setEstado($estado) {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return string
     */
    public function getEstado() {
        return $this->estado;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return OrdenTrabajo
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return OrdenTrabajo
     */
    public function setUpdated($updated) {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated() {
        return $this->updated;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return OrdenTrabajo
     */
    public function setDeletedAt($deletedAt) {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt() {
        return $this->deletedAt;
    }

    /**
     * Set tecnico
     *
     * @param \ConfigBundle\Entity\Usuario $tecnico
     * @return OrdenTrabajo
     */
    public function setTecnico(\ConfigBundle\Entity\Usuario $tecnico = null) {
        $this->tecnico = $tecnico;

        return $this;
    }

    /**
     * Get tecnico
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getTecnico() {
        return $this->tecnico;
    }

    /**
     * Set requerimiento
     *
     * @param \AppBundle\Entity\Requerimiento $requerimiento
     * @return OrdenTrabajo
     */
    public function setRequerimiento(\AppBundle\Entity\Requerimiento $requerimiento = null) {
        $this->requerimiento = $requerimiento;

        return $this;
    }

    /**
     * Get requerimiento
     *
     * @return \AppBundle\Entity\Requerimiento
     */
    public function getRequerimiento() {
        return $this->requerimiento;
    }

    /**
     * Add detalles
     *
     * @param \AppBundle\Entity\OrdenTrabajoDetalle $detalles
     * @return OrdenTrabajo
     */
    public function addDetalle(\AppBundle\Entity\OrdenTrabajoDetalle $detalles) {
        $detalles->setOrdenTrabajo($this);
        $this->detalles[] = $detalles;

        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \AppBundle\Entity\OrdenTrabajoDetalle $detalles
     */
    public function removeDetalle(\AppBundle\Entity\OrdenTrabajoDetalle $detalles) {
        $this->detalles->removeElement($detalles);
    }

    /**
     * Get detalles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDetalles() {
        return $this->detalles;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return OrdenTrabajo
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null) {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return OrdenTrabajo
     */
    public function setUpdatedBy(\ConfigBundle\Entity\Usuario $updatedBy = null) {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getUpdatedBy() {
        return $this->updatedBy;
    }

    /**
     * Add tareas
     *
     * @param \AppBundle\Entity\Tarea $tareas
     * @return OrdenTrabajo
     */
    public function addTarea(\AppBundle\Entity\Tarea $tareas) {
        $this->tareas[] = $tareas;

        return $this;
    }

    /**
     * Remove tareas
     *
     * @param \AppBundle\Entity\Tarea $tareas
     */
    public function removeTarea(\AppBundle\Entity\Tarea $tareas) {
        $this->tareas->removeElement($tareas);
    }

    /**
     * Get tareas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTareas() {
        return $this->tareas;
    }

    public function getTareasGenerales() {
        if ($this->getTareas()) {
            foreach ($this->getTareas() as $tarea) {
                if (count($tarea->getOrdenTrabajoDetalles()) == 0) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getCountTareasGenerales() {
        $cnt = 0;
        if ($this->getTareas()) {
            foreach ($this->getTareas() as $tarea) {
                if (count($tarea->getOrdenTrabajoDetalles()) == 0) {
                    $cnt = $cnt + 1;
                }
            }
        }
        return $cnt;
    }

    public function getReubicacionEquipo() {
        if ($this->getTareas()) {
            foreach ($this->getTareas() as $tarea) {
                if ($tarea->getTipoTarea()->getAbreviatura() == 'RE') {
                    return true;
                }
            }
        }
        return false;
    }

    public function getReemplazoEquipo() {
        if ($this->getTareas()) {
            foreach ($this->getTareas() as $tarea) {
                if ($tarea->getTipoTarea()->getAbreviatura() == 'CE') {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Set altaPrioridad
     *
     * @param boolean $altaPrioridad
     * @return OrdenTrabajo
     */
    public function setAltaPrioridad($altaPrioridad) {
        $this->altaPrioridad = $altaPrioridad;

        return $this;
    }

    /**
     * Get altaPrioridad
     *
     * @return boolean
     */
    public function getAltaPrioridad() {
        return $this->altaPrioridad;
    }

    /**
     * Set jira
     *
     * @param string $jira
     * @return OrdenTrabajo
     */
    public function setJira($jira) {
        $this->jira = $jira;

        return $this;
    }

    /**
     * Get jira
     *
     * @return string
     */
    public function getJira() {
        return $this->jira;
    }

    /**
     * Add documentos
     *
     * @param \AppBundle\Entity\Documentacion $documentos
     * @return OrdenTrabajo
     */
    public function addDocumento(\AppBundle\Entity\Documentacion $documentos) {
        $documentos->setOrdenTrabajo($this);
        $this->documentos[] = $documentos;

        return $this;
    }

    /**
     * Remove documentos
     *
     * @param \AppBundle\Entity\Documentacion $documentos
     */
    public function removeDocumento(\AppBundle\Entity\Documentacion $documentos) {
        $this->documentos->removeElement($documentos);
    }

    /**
     * Get documentos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDocumentos() {
        return $this->documentos;
    }

}