<?php

namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\Requerimiento
 * @ORM\Table(name="requerimiento")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SoporteRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\Loggable()
 */
class Requerimiento {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var datetime $fechaRequerimiento
     * @ORM\Column(name="fecha_requerimiento", type="datetime", nullable=false)
     * @Gedmo\Versioned()
     */
    private $fechaRequerimiento;

    /**
     * @var string $nroIncidencia
     * @ORM\Column(name="nro_incidencia", type="string",length=20, nullable=true)
     * @Gedmo\Versioned()
     */
    protected $nroIncidencia;

    /**
     * @var string $jira
     * @ORM\Column(name="jira", type="string",length=20, nullable=true)
     * @Gedmo\Versioned()
     */
    protected $jira;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\TipoSoporte")
     * @ORM\JoinColumn(name="tipo_soporte_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $tipoSoporte;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento")
     * @ORM\JoinColumn(name="solicitante_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $solicitante;

    /**
     * @var string $responsable
     * @ORM\Column(name="responsable", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $responsable;

    /**
     * @var text $descripcion
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $descripcion;

    /**
     * @var text $textoActaRecepcion
     * @ORM\Column(name="texto_acta_recepcion", type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $textoActaRecepcion;

    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     * @Gedmo\Versioned()
     */
    /* SIN ASIGNAR - ASIGNADO - FINALIZADO - CANCELADO */
    protected $estado = 'SIN ASIGNAR';

    /**
     * @ORM\Column(name="alta_prioridad", type="boolean", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $altaPrioridad = false;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Estado")
     * @ORM\JoinColumn(name="estado_equipo_id", referencedColumnName="id")
     */
    protected $estadoEquipo;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento")
     * @ORM\JoinColumn(name="departamento_equipo_id", referencedColumnName="id")
     */
    protected $departamentoEquipo;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Piso")
     * @ORM\JoinColumn(name="piso_equipo_id", referencedColumnName="id")
     */
    protected $pisoEquipo;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\OrdenTrabajo", mappedBy="requerimiento")
     * OT asociadas
     */
    protected $ordentrabajoAsociadas;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\RequerimientoDetalle", mappedBy="requerimiento", orphanRemoval=true,cascade={"persist", "remove"})
     */
    protected $detalles;

    /**
     * @var text $descripcionFinalizacion
     * @ORM\Column(name="descripcion_finalizacion", type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $descripcionFinalizacion;

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

    /**
     * Constructor
     */
    public function __construct() {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString() {
        return strval($this->getId());
    }

    public function getOrdentrabajoAsociadasTxt() {
        $txt = '';
        foreach ($this->getOrdentrabajoAsociadas() as $ot) {
            if ($txt != '') {
                $txt = $txt . ',';
            }
            $txt = $txt . ' ' . $ot->getTecnico()->getNombre();
        }
        return $txt;
    }

    public function getOTid() {
        foreach ($this->getOrdentrabajoAsociadas() as $ot) {
            if ($ot->getEstado() == 'ABIERTO') {
                return $ot->getId();
            }
        }
        return false;
    }

    public function getTiempo() {
        $inicio = $this->getFechaRequerimiento();
        $hoy = new \DateTime();
        $fin = ( $this->getEstado() == 'SIN ASIGNAR' || $this->getEstado() == 'ASIGNADO') ? $hoy : $this->getUpdated();
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

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set fechaRequerimiento
     *
     * @param \DateTime $fechaRequerimiento
     * @return Requerimiento
     */
    public function setFechaRequerimiento($fechaRequerimiento) {
        $this->fechaRequerimiento = $fechaRequerimiento;
        return $this;
    }

    /**
     * Get fechaRequerimiento
     *
     * @return \DateTime
     */
    public function getFechaRequerimiento() {
        return $this->fechaRequerimiento;
    }

    /**
     * Set nroIncidencia
     *
     * @param string $nroIncidencia
     * @return Requerimiento
     */
    public function setNroIncidencia($nroIncidencia) {
        $this->nroIncidencia = $nroIncidencia;

        return $this;
    }

    /**
     * Get nroIncidencia
     *
     * @return string
     */
    public function getNroIncidencia() {
        return $this->nroIncidencia;
    }

    /**
     * Set responsable
     *
     * @param string $responsable
     * @return Requerimiento
     */
    public function setResponsable($responsable) {
        $this->responsable = $responsable;

        return $this;
    }

    /**
     * Get responsable
     *
     * @return string
     */
    public function getResponsable() {
        return $this->responsable;
    }

    /**
     * Set descripcion
     *
     * @param text $descripcion
     * @return Requerimiento
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
     * @return Requerimiento
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
     * @return Requerimiento
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
     * @return Requerimiento
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
     * @return Requerimiento
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
     * Set tipoSoporte
     *
     * @param \ConfigBundle\Entity\TipoSoporte $tipoSoporte
     * @return Requerimiento
     */
    public function setTipoSoporte(\ConfigBundle\Entity\TipoSoporte $tipoSoporte = null) {
        $this->tipoSoporte = $tipoSoporte;

        return $this;
    }

    /**
     * Get tipoSoporte
     *
     * @return \ConfigBundle\Entity\TipoSoporte
     */
    public function getTipoSoporte() {
        return $this->tipoSoporte;
    }

    /**
     * Set solicitante
     *
     * @param \ConfigBundle\Entity\Departamento $solicitante
     * @return Requerimiento
     */
    public function setSolicitante(\ConfigBundle\Entity\Departamento $solicitante = null) {
        $this->solicitante = $solicitante;

        return $this;
    }

    /**
     * Get solicitante
     *
     * @return \ConfigBundle\Entity\Departamento
     */
    public function getSolicitante() {
        return $this->solicitante;
    }

    /**
     * Add detalles
     *
     * @param \AppBundle\Entity\RequerimientoDetalle $detalles
     * @return Requerimiento
     */
    public function addDetalle(\AppBundle\Entity\RequerimientoDetalle $detalles) {
        $detalles->setRequerimiento($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \AppBundle\Entity\RequerimientoDetalle $detalles
     */
    public function removeDetalle(\AppBundle\Entity\RequerimientoDetalle $detalles) {
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
     * @return Requerimiento
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
     * @return Requerimiento
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
     * Add ordentrabajoAsociadas
     *
     * @param \AppBundle\Entity\OrdenTrabajo $ordentrabajoAsociadas
     * @return Requerimiento
     */
    public function addOrdentrabajoAsociada(\AppBundle\Entity\OrdenTrabajo $ordentrabajoAsociadas) {
        $this->ordentrabajoAsociadas[] = $ordentrabajoAsociadas;

        return $this;
    }

    /**
     * Remove ordentrabajoAsociadas
     *
     * @param \AppBundle\Entity\OrdenTrabajo $ordentrabajoAsociadas
     */
    public function removeOrdentrabajoAsociada(\AppBundle\Entity\OrdenTrabajo $ordentrabajoAsociadas) {
        $this->ordentrabajoAsociadas->removeElement($ordentrabajoAsociadas);
    }

    /**
     * Get ordentrabajoAsociadas
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrdentrabajoAsociadas() {
        return $this->ordentrabajoAsociadas;
    }

    /**
     * Set altaPrioridad
     *
     * @param boolean $altaPrioridad
     * @return Requerimiento
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
     * Set estadoEquipo
     *
     * @param \ConfigBundle\Entity\Estado $estadoEquipo
     * @return Requerimiento
     */
    public function setEstadoEquipo(\ConfigBundle\Entity\Estado $estadoEquipo = null) {
        $this->estadoEquipo = $estadoEquipo;

        return $this;
    }

    /**
     * Get estadoEquipo
     *
     * @return \ConfigBundle\Entity\Estado
     */
    public function getEstadoEquipo() {
        return $this->estadoEquipo;
    }

    /**
     * Set departamentoEquipo
     *
     * @param \ConfigBundle\Entity\Departamento $departamentoEquipo
     * @return Requerimiento
     */
    public function setDepartamentoEquipo(\ConfigBundle\Entity\Departamento $departamentoEquipo = null) {
        $this->departamentoEquipo = $departamentoEquipo;

        return $this;
    }

    /**
     * Get departamentoEquipo
     *
     * @return \ConfigBundle\Entity\Departamento
     */
    public function getDepartamentoEquipo() {
        return $this->departamentoEquipo;
    }

    /**
     * Set pisoEquipo
     *
     * @param \ConfigBundle\Entity\Piso $pisoEquipo
     * @return Requerimiento
     */
    public function setPisoEquipo(\ConfigBundle\Entity\Piso $pisoEquipo = null) {
        $this->pisoEquipo = $pisoEquipo;

        return $this;
    }

    /**
     * Get pisoEquipo
     *
     * @return \ConfigBundle\Entity\Piso
     */
    public function getPisoEquipo() {
        return $this->pisoEquipo;
    }

    /**
     * Set jira
     *
     * @param string $jira
     * @return Requerimiento
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
     * Set descripcionFinalizacion
     *
     * @param string $descripcionFinalizacion
     * @return Requerimiento
     */
    public function setDescripcionFinalizacion($descripcionFinalizacion) {
        $this->descripcionFinalizacion = $descripcionFinalizacion;

        return $this;
    }

    /**
     * Get descripcionFinalizacion
     *
     * @return string
     */
    public function getDescripcionFinalizacion() {
        return $this->descripcionFinalizacion;
    }

    public function getCuentaDeEquipos($tipo) {
        $cantidad = 0;
        foreach ($this->getDetalles() as $det) {
            if ($det->getEquipo()->getTipo()->getId() == $tipo) {
                $cantidad += 1;
            }
        }
        foreach ($this->getOrdentrabajoAsociadas() as $ot) {
            foreach ($ot->getDetalles() as $det) {
                if (!$det->getRequerimientoDetalle() && $det->getEquipo()->getTipo()->getId() == $tipo) {
                    $tiporecambio = 'OUT';
                    foreach ($det->getTareas() as $tar) {
                        if ($tar->getTipoTarea()->getAbreviatura() == 'CE') {
                            $tiporecambio = 'IN';
                        }
                    }
                    if ($det->getTipoRecambio() == $tiporecambio) {
                        $cantidad += 1;
                    }
                }
            }
        }
        return $cantidad;
    }

    /**
     * Set textoActaRecepcion
     * @param string $textoActaRecepcion
     * @return Requerimiento
     */
    public function setTextoActaRecepcion($textoActaRecepcion) {
        $this->textoActaRecepcion = $textoActaRecepcion;
        return $this;
    }

    /**
     * Get textoActaRecepcion
     * @return string
     */
    public function getTextoActaRecepcion() {
        return $this->textoActaRecepcion;
    }

}