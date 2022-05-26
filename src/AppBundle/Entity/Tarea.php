<?php

namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use ConfigBundle\Controller\UtilsController;

/**
 * AppBundle\Entity\Tarea
 * @ORM\Table(name="tarea")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\SoporteRepository")
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Tarea {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var datetime $fecha
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     */
    private $fecha;

    /**
     * @var string $descripcion
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     */
    protected $descripcion;

    /**
     * @var string $textoAdicional
     * @ORM\Column(name="texto_adicional", type="text", nullable=true)
     */
    protected $textoAdicional;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\TipoTarea")
     * @ORM\JoinColumn(name="tipo_tarea_id", referencedColumnName="id")
     */
    protected $tipoTarea;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\OrdenTrabajo",inversedBy="tareas" )
     * @ORM\JoinColumn(name="orden_trabajo_id", referencedColumnName="id")
     */
    protected $ordenTrabajo;

    /**
     * @ORM\ManyToMany(targetEntity="AppBundle\Entity\OrdenTrabajoDetalle",inversedBy="tareas")
     * @ORM\JoinTable(name="equipo_x_tarea",
     *      joinColumns={@ORM\JoinColumn(name="tarea_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="orden_trabajo_detalle_id", referencedColumnName="id")}
     * )
     */
    private $ordenTrabajoDetalles;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento")
     * @ORM\JoinColumn(name="equipo_ubicacion_final_id", referencedColumnName="id")
     */
    protected $equipoUbicacionFinal;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\InsumoxTarea", mappedBy="tarea",cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $insumos;

    /**
     * @var string $conceptoEntrega
     * @ORM\Column(name="concepto_entrega", type="string", nullable=true)
     */
    protected $conceptoEntrega;

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

    public function getFechaLarga() {
        return UtilsController::longDateSpanish($this->getFecha());
    }

    /**
     * lista de insumos aprovados para impresion de OT
     */
    public function getInsumosAprobados() {
        $insumos = array();
        if ($this->getTipoTarea()->getAbreviatura() == 'SI') {
            foreach ($this->getInsumos() as $insxtarea) {
                if ($insxtarea->getCantidadAprobada() > 0) {
                    $insumos[] = array('cantidad' => $insxtarea->getCantidadAprobada(),
                        'descripcion' => $insxtarea->getDescripcion());
                }
            }
        }
        return $insumos;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->ordenTrabajoDetalles = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return Tarea
     */
    public function setFecha($fecha) {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha() {
        return $this->fecha;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return Tarea
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
     * Set created
     *
     * @param \DateTime $created
     * @return Tarea
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
     * @return Tarea
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
     * @return Tarea
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
     * Set tipoTarea
     *
     * @param \ConfigBundle\Entity\TipoTarea $tipoTarea
     * @return Tarea
     */
    public function setTipoTarea(\ConfigBundle\Entity\TipoTarea $tipoTarea = null) {
        $this->tipoTarea = $tipoTarea;

        return $this;
    }

    /**
     * Get tipoTarea
     *
     * @return \ConfigBundle\Entity\TipoTarea
     */
    public function getTipoTarea() {
        return $this->tipoTarea;
    }

    /**
     * Set ordenTrabajo
     *
     * @param \AppBundle\Entity\OrdenTrabajo $ordenTrabajo
     * @return Tarea
     */
    public function setOrdenTrabajo(\AppBundle\Entity\OrdenTrabajo $ordenTrabajo = null) {
        $this->ordenTrabajo = $ordenTrabajo;

        return $this;
    }

    /**
     * Get ordenTrabajo
     *
     * @return \AppBundle\Entity\OrdenTrabajo
     */
    public function getOrdenTrabajo() {
        return $this->ordenTrabajo;
    }

    /**
     * Add ordenTrabajoDetalles
     *
     * @param \AppBundle\Entity\OrdenTrabajoDetalle $ordenTrabajoDetalles
     * @return Tarea
     */
    public function addOrdenTrabajoDetalle(\AppBundle\Entity\OrdenTrabajoDetalle $ordenTrabajoDetalles) {
        $this->ordenTrabajoDetalles[] = $ordenTrabajoDetalles;

        return $this;
    }

    /**
     * Remove ordenTrabajoDetalles
     *
     * @param \AppBundle\Entity\OrdenTrabajoDetalle $ordenTrabajoDetalles
     */
    public function removeOrdenTrabajoDetalle(\AppBundle\Entity\OrdenTrabajoDetalle $ordenTrabajoDetalles) {
        $this->ordenTrabajoDetalles->removeElement($ordenTrabajoDetalles);
    }

    /**
     * Get ordenTrabajoDetalles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrdenTrabajoDetalles() {
        return $this->ordenTrabajoDetalles;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Tarea
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
     * @return Tarea
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
     * Set equipoUbicacionFinal
     *
     * @param \ConfigBundle\Entity\Departamento $equipoUbicacionFinal
     * @return Tarea
     */
    public function setEquipoUbicacionFinal(\ConfigBundle\Entity\Departamento $equipoUbicacionFinal = null) {
        $this->equipoUbicacionFinal = $equipoUbicacionFinal;

        return $this;
    }

    /**
     * Get equipoUbicacionFinal
     *
     * @return \AppBundle\Entity\EquipoUbicacion
     */
    public function getEquipoUbicacionFinal() {
        return $this->equipoUbicacionFinal;
    }

    /**
     * Add insumosxTarea
     *
     * @param \AppBundle\Entity\InsumoxTarea $insumosxTarea
     * @return Tarea
     */
    public function addInsumosxTarea(\AppBundle\Entity\InsumoxTarea $insumosxTarea) {
        $insumosxTarea->setTarea($this);
        $this->insumosxTarea[] = $insumosxTarea;
        return $this;
    }

    /**
     * Remove insumosxTarea
     *
     * @param \AppBundle\Entity\InsumoxTarea $insumosxTarea
     */
    public function removeInsumosxTarea(\AppBundle\Entity\InsumoxTarea $insumosxTarea) {
        $this->insumosxTarea->removeElement($insumosxTarea);
    }

    /**
     * Get insumosxTarea
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInsumosxTarea() {
        return $this->insumosxTarea;
    }

    /**
     * Add insumos
     *
     * @param \AppBundle\Entity\InsumoxTarea $insumos
     * @return Tarea
     */
    public function addInsumo(\AppBundle\Entity\InsumoxTarea $insumos) {
        $insumos->setTarea($this);
        $this->insumos[] = $insumos;

        return $this;
    }

    /**
     * Remove insumos
     *
     * @param \AppBundle\Entity\InsumoxTarea $insumos
     */
    public function removeInsumo(\AppBundle\Entity\InsumoxTarea $insumos) {
        $this->insumos->removeElement($insumos);
    }

    /**
     * Get insumos
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getInsumos() {
        return $this->insumos;
    }

    /**
     * Set conceptoEntrega
     *
     * @param string $conceptoEntrega
     * @return Tarea
     */
    public function setConceptoEntrega($conceptoEntrega) {
        $this->conceptoEntrega = $conceptoEntrega;

        return $this;
    }

    /**
     * Get conceptoEntrega
     *
     * @return string
     */
    public function getConceptoEntrega() {
        return $this->conceptoEntrega;
    }

    /**
     * Set textoAdicional
     *
     * @param string $textoAdicional
     * @return Tarea
     */
    public function setTextoAdicional($textoAdicional) {
        $this->textoAdicional = $textoAdicional;

        return $this;
    }

    /**
     * Get textoAdicional
     *
     * @return string
     */
    public function getTextoAdicional() {
        return $this->textoAdicional;
    }

}