<?php

namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\InsumoxTarea
 * @ORM\Table(name="insumo_x_tarea")
 * @ORM\Entity()
 */
class InsumoxTarea {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Tarea",inversedBy="insumos" )
     * @ORM\JoinColumn(name="tarea_id", referencedColumnName="id")
     */
    protected $tarea;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Insumo")
     * @ORM\JoinColumn(name="insumo_id", referencedColumnName="id")
     */
    protected $insumo;

    /**
     * @var string $descripcion
     * @ORM\Column(name="descripcion", type="string", nullable=true)
     */
    protected $descripcion;

    /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=2)
     */
    protected $cantidad;

    /**
     * @ORM\Column(name="fecha_autorizado", type="datetime", nullable=true)
     */
    private $fechaAutorizado;

    /**
     * @var integer $cantidadAprobada
     * @ORM\Column(name="cantidad_aprobada", type="decimal", scale=2, nullable=true)
     */
    protected $cantidadAprobada = NULL;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario" )
     * @ORM\JoinColumn(name="autorizante_id", referencedColumnName="id")
     */
    protected $autorizante;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\InsumoEntregaDetalle", mappedBy="insumoxTarea")
     */
    protected $insumoEntregaDetalle;

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

    public function getNroOT() {
        return '#' . $this->getTarea()->getOrdenTrabajo()->getId();
    }

    public function getCantidadAprobadaTxt() {
        if ($this->getCantidadAprobada()) {
            if ($this->getCantidadAprobada() > 0 && $this->getCantidadAprobada() < $this->getCantidad()) {
                return '(' . $this->getCantidadAprobada() . ')';
            }
        }
        return '';
    }

    public function getDescripcionTxt() {
        if ($this->getInsumo()) {
            return $this->getInsumo()->getTexto();
        }
        else {
            return $this->getDescripcion();
        }
    }

    public function getEstado($icon = null) {
        //<i class="fa fa-hourglass-half" style="color:blue" title="Aprobación Pendiente"></i>
        if ($this->fechaAutorizado) {
            if ($this->getCantidadAprobada() == 0) {
                $html = '<i class="fa fa-close" style="color:red" title="Rechazado ' . $this->getFechaAutorizado()->format('d/m/Y') . ' ' . $this->getAutorizante()->getUsername() . '"></i>';
                return ($icon) ? $html : 'R';
            }
            else {
                if ($this->getCantidadAprobada() < $this->getCantidad()) {
                    $html = '<i class="fa fa-check" style="color:orange" title="Aprobado parcial ' . $this->getCantidadAprobada() . ' - ' . $this->getFechaAutorizado()->format('d/m/Y') . ' ' . $this->getAutorizante()->getUsername() . '"></i>';
                    return ($icon) ? $html : 'AP';
                }
                else {
                    $html = '<i class="fa fa-check" style="color:#00ca6d" title="Aprobado ' . $this->getFechaAutorizado()->format('d/m/Y') . ' ' . $this->getAutorizante()->getUsername() . '"></i>';
                    return ($icon) ? $html : 'AT';
                }
            }
        }
        else {
            return ($icon) ? '<i class="fa fa-hourglass-half" style="color:blue" title="Aprobación Pendiente"></i>' : 'P';
        }
    }

    public function getEquipo() {
        if (count($this->getTarea()->getOrdenTrabajoDetalles()) > 0) {
            return $this->getTarea()->getOrdenTrabajoDetalles()[0]->getEquipo()->getTextoOT();
        }
        else
            return '';
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
     * Set fechaAutorizado
     *
     * @param \DateTime $fechaAutorizado
     * @return InsumoxTarea
     */
    public function setFechaAutorizado($fechaAutorizado) {
        $this->fechaAutorizado = $fechaAutorizado;

        return $this;
    }

    /**
     * Get fechaAutorizado
     *
     * @return \DateTime
     */
    public function getFechaAutorizado() {
        return $this->fechaAutorizado;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return InsumoxTarea
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
     * @return InsumoxTarea
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
     * Set tarea
     *
     * @param \AppBundle\Entity\Tarea $tarea
     * @return InsumoxTarea
     */
    public function setTarea(\AppBundle\Entity\Tarea $tarea = null) {
        $this->tarea = $tarea;
        return $this;
    }

    /**
     * Get tarea
     *
     * @return \AppBundle\Entity\Tarea
     */
    public function getTarea() {
        return $this->tarea;
    }

    /**
     * Set insumo
     *
     * @param \AppBundle\Entity\Insumo $insumo
     * @return InsumoxTarea
     */
    public function setInsumo(\AppBundle\Entity\Insumo $insumo = null) {
        $this->insumo = $insumo;

        return $this;
    }

    /**
     * Get insumo
     *
     * @return \AppBundle\Entity\Insumo
     */
    public function getInsumo() {
        return $this->insumo;
    }

    /**
     * Set autorizante
     *
     * @param \ConfigBundle\Entity\Usuario $autorizante
     * @return InsumoxTarea
     */
    public function setAutorizante(\ConfigBundle\Entity\Usuario $autorizante = null) {
        $this->autorizante = $autorizante;

        return $this;
    }

    /**
     * Get autorizante
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getAutorizante() {
        return $this->autorizante;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return InsumoxTarea
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
     * @return InsumoxTarea
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
     * Set cantidad
     *
     * @param string $cantidad
     * @return InsumoxTarea
     */
    public function setCantidad($cantidad) {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return string
     */
    public function getCantidad() {
        return $this->cantidad;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return InsumoxTarea
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
     * Set cantidadAprobada
     *
     * @param string $cantidadAprobada
     * @return InsumoxTarea
     */
    public function setCantidadAprobada($cantidadAprobada) {
        $this->cantidadAprobada = $cantidadAprobada;

        return $this;
    }

    /**
     * Get cantidadAprobada
     *
     * @return string
     */
    public function getCantidadAprobada() {
        return $this->cantidadAprobada;
    }


    /**
     * Set insumoEntregaDetalle
     *
     * @param \AppBundle\Entity\InsumoEntregaDetalle $insumoEntregaDetalle
     * @return InsumoxTarea
     */
    public function setInsumoEntregaDetalle(\AppBundle\Entity\InsumoEntregaDetalle $insumoEntregaDetalle = null)
    {
        $this->insumoEntregaDetalle = $insumoEntregaDetalle;

        return $this;
    }

    /**
     * Get insumoEntregaDetalle
     *
     * @return \AppBundle\Entity\InsumoEntregaDetalle 
     */
    public function getInsumoEntregaDetalle()
    {
        return $this->insumoEntregaDetalle;
    }
}
