<?php

namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\EquipoUbicacion
 * @ORM\Table(name="equipo_ubicacion")
 * @ORM\Entity()
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\Loggable()
 */
class EquipoUbicacion {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Equipo", inversedBy="ubicaciones")
     * @ORM\JoinColumn(name="equipo_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $equipo;

    /**
     * @var string $redIp
     * @ORM\Column(name="red_ip", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $redIp;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Ubicacion")
     * @ORM\JoinColumn(name="ubicacion_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $ubicacion;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento",inversedBy="equipos")
     * @ORM\JoinColumn(name="departamento_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $departamento;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Edificio")
     * @ORM\JoinColumn(name="edificio_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $edificio;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Piso")
     * @ORM\JoinColumn(name="piso_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $piso;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\ConceptoEntrega")
     * @ORM\JoinColumn(name="concepto_entrega_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $conceptoEntrega;

    /**
     * @var date $fechaEntrega
     * @ORM\Column(name="fecha_entrega", type="date", nullable=true)
     * @Gedmo\Versioned()
     */
    private $fechaEntrega;

    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $observaciones;

    /**
     * @ORM\Column(name="actual", type="boolean")
     */
    protected $actual = true;

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
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\RequerimientoDetalle", inversedBy="equipoUbicacionRequerimiento",cascade={"persist"})
     * @ORM\JoinColumn(name="requerimiento_detalle_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $requerimientoDetalle;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\OrdenTrabajoDetalle", inversedBy="equipoUbicacionOrdenTrabajo",cascade={"persist"})
     * @ORM\JoinColumn(name="orden_trabajo_detalle_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $ordenTrabajoDetalle;

    public function __toString() {
        return $this->getUbicacion();
    }

    public function getTexto() {
        if ($this->getDepartamento()) {
            if ($this->getDepartamento()->getEdificio()) {
                if ($this->getDepartamento()->getEdificio()->getUbicacion()) {
                    $cadena = $this->getDepartamento()->getEdificio()->getUbicacion()->getAbreviatura() . ' - ' . $this->getDepartamento()->getEdificio() . ' - ' . $this->getDepartamento() . ' - ' . $this->getPiso();
                }
                else {
                    $cadena = "Sin ubicacion - verificar!";
                }
            }
            else {
                $cadena = "Sin edificio - verificar!";
            }
        }
        else {
            $cadena = 'Sin Departamento asignado';
        }
        return $cadena;
    }

    public function getTextoParaBienes() {
        if ($this->getDepartamento()) {
            $cadena = $this->getDepartamento()->getEdificio()->getUbicacion()->getAbreviatura() . ' - ' . $this->getDepartamento()->getEdificio() . ' - ' . $this->getDepartamento();
        }
        else {
            $cadena = 'Sin Departamento asignado';
        }
        return $cadena;
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
     * Set fechaEntrega
     *
     * @param \DateTime $fechaEntrega
     * @return EquipoUbicacion
     */
    public function setFechaEntrega($fechaEntrega) {
        $this->fechaEntrega = $fechaEntrega;

        return $this;
    }

    /**
     * Get fechaEntrega
     *
     * @return \DateTime
     */
    public function getFechaEntrega() {
        return $this->fechaEntrega;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return EquipoUbicacion
     */
    public function setObservaciones($observaciones) {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string
     */
    public function getObservaciones() {
        return $this->observaciones;
    }

    /**
     * Set actual
     *
     * @param boolean $actual
     * @return EquipoUbicacion
     */
    public function setActual($actual) {
        $this->actual = $actual;

        return $this;
    }

    /**
     * Get actual
     *
     * @return boolean
     */
    public function getActual() {
        return $this->actual;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return EquipoUbicacion
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
     * @return EquipoUbicacion
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
     * @return EquipoUbicacion
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
     * Set equipo
     *
     * @param \AppBundle\Entity\Equipo $equipo
     * @return EquipoUbicacion
     */
    public function setEquipo(\AppBundle\Entity\Equipo $equipo = null) {
        $this->equipo = $equipo;

        return $this;
    }

    /**
     * Get equipo
     *
     * @return \AppBundle\Entity\Equipo
     */
    public function getEquipo() {
        return $this->equipo;
    }

    /**
     * Set ubicacion
     *
     * @param \ConfigBundle\Entity\Ubicacion $ubicacion
     * @return EquipoUbicacion
     */
    public function setUbicacion(\ConfigBundle\Entity\Ubicacion $ubicacion = null) {
        $this->ubicacion = $ubicacion;

        return $this;
    }

    /**
     * Get ubicacion
     *
     * @return \ConfigBundle\Entity\Ubicacion
     */
    public function getUbicacion() {
        return $this->ubicacion;
    }

    /**
     * Set departamento
     *
     * @param \ConfigBundle\Entity\Departamento $departamento
     * @return EquipoUbicacion
     */
    public function setDepartamento(\ConfigBundle\Entity\Departamento $departamento = null) {
        $this->departamento = $departamento;

        return $this;
    }

    /**
     * Get departamento
     *
     * @return \ConfigBundle\Entity\Departamento
     */
    public function getDepartamento() {
        return $this->departamento;
    }

    /**
     * Set edificio
     *
     * @param \ConfigBundle\Entity\Edificio $edificio
     * @return EquipoUbicacion
     */
    public function setEdificio(\ConfigBundle\Entity\Edificio $edificio = null) {
        $this->edificio = $edificio;

        return $this;
    }

    /**
     * Get edificio
     *
     * @return \ConfigBundle\Entity\Edificio
     */
    public function getEdificio() {
        return $this->edificio;
    }

    /**
     * Set piso
     *
     * @param \ConfigBundle\Entity\Piso $piso
     * @return EquipoUbicacion
     */
    public function setPiso(\ConfigBundle\Entity\Piso $piso = null) {
        $this->piso = $piso;

        return $this;
    }

    /**
     * Get piso
     *
     * @return \ConfigBundle\Entity\Piso
     */
    public function getPiso() {
        return $this->piso;
    }

    /**
     * Set conceptoEntrega
     *
     * @param \ConfigBundle\Entity\ConceptoEntrega $conceptoEntrega
     * @return EquipoUbicacion
     */
    public function setConceptoEntrega(\ConfigBundle\Entity\ConceptoEntrega $conceptoEntrega = null) {
        $this->conceptoEntrega = $conceptoEntrega;

        return $this;
    }

    /**
     * Get conceptoEntrega
     *
     * @return \ConfigBundle\Entity\ConceptoEntrega
     */
    public function getConceptoEntrega() {
        return $this->conceptoEntrega;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return EquipoUbicacion
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
     * @return EquipoUbicacion
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
     * Set redIp
     *
     * @param string $redIp
     * @return EquipoUbicacion
     */
    public function setRedIp($redIp) {
        $this->redIp = $redIp;

        return $this;
    }

    /**
     * Get redIp
     *
     * @return string
     */
    public function getRedIp() {
        return $this->redIp;
    }

    /**
     * Set ubicacionRequerimiento
     *
     * @param \AppBundle\Entity\RequerimientoDetalle $ubicacionRequerimiento
     * @return EquipoUbicacion
     */
    public function setUbicacionRequerimiento(\AppBundle\Entity\RequerimientoDetalle $ubicacionRequerimiento = null) {
        $this->ubicacionRequerimiento = $ubicacionRequerimiento;

        return $this;
    }

    /**
     * Get ubicacionRequerimiento
     *
     * @return \AppBundle\Entity\RequerimientoDetalle
     */
    public function getUbicacionRequerimiento() {
        return $this->ubicacionRequerimiento;
    }

    /**
     * Set requerimientoDetalle
     *
     * @param \AppBundle\Entity\RequerimientoDetalle $requerimientoDetalle
     * @return EquipoUbicacion
     */
    public function setRequerimientoDetalle(\AppBundle\Entity\RequerimientoDetalle $requerimientoDetalle = null) {
        $this->requerimientoDetalle = $requerimientoDetalle;

        return $this;
    }

    /**
     * Get requerimientoDetalle
     *
     * @return \AppBundle\Entity\RequerimientoDetalle
     */
    public function getRequerimientoDetalle() {
        return $this->requerimientoDetalle;
    }

    /**
     * Set ordenTrabajoDetalle
     *
     * @param \AppBundle\Entity\OrdenTrabajoDetalle $ordenTrabajoDetalle
     * @return EquipoUbicacion
     */
    public function setOrdenTrabajoDetalle(\AppBundle\Entity\OrdenTrabajoDetalle $ordenTrabajoDetalle = null) {
        $this->ordenTrabajoDetalle = $ordenTrabajoDetalle;

        return $this;
    }

    /**
     * Get ordenTrabajoDetalle
     *
     * @return \AppBundle\Entity\OrdenTrabajoDetalle
     */
    public function getOrdenTrabajoDetalle() {
        return $this->ordenTrabajoDetalle;
    }

}