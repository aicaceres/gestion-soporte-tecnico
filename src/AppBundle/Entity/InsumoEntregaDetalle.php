<?php

namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\InsumoEntregaDetalle
 * @ORM\Table(name="insumo_entrega_detalle")
 * @ORM\Entity()
 * @Gedmo\Loggable()
 */
class InsumoEntregaDetalle {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Insumo", inversedBy="entregas")
     * @ORM\JoinColumn(name="insumo_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $insumo;

    /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=2)
     */
    protected $cantidad;

    /**
     * @var string $descripcion
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $descripcion;

    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\InsumoxTarea", inversedBy="insumoEntregaDetalle")
     * @ORM\JoinColumn(name="insumo_x_tarea_id", referencedColumnName="id")
     */
    protected $insumoxTarea;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\InsumoEntrega", inversedBy="detalles",cascade={"persist"})
     * @ORM\JoinColumn(name="insumo_entrega_id", referencedColumnName="id")
     */
    protected $entrega;

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
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return InsumoEntregaDetalle
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
     * @return InsumoEntregaDetalle
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
     * @return InsumoEntregaDetalle
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
     * Set insumo
     *
     * @param \AppBundle\Entity\Insumo $insumo
     * @return InsumoEntregaDetalle
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
     * Set entrega
     *
     * @param \AppBundle\Entity\InsumoEntrega $entrega
     * @return InsumoEntregaDetalle
     */
    public function setEntrega(\AppBundle\Entity\InsumoEntrega $entrega = null) {
        $this->entrega = $entrega;

        return $this;
    }

    /**
     * Get entrega
     *
     * @return \AppBundle\Entity\InsumoEntrega
     */
    public function getEntrega() {
        return $this->entrega;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return InsumoEntregaDetalle
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
     * @return InsumoEntregaDetalle
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
     * @return InsumoEntregaDetalle
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
     * Set insumoxTarea
     *
     * @param \AppBundle\Entity\InsumoxTarea $insumoxTarea
     * @return InsumoEntregaDetalle
     */
    public function setInsumoxTarea(\AppBundle\Entity\InsumoxTarea $insumoxTarea = null)
    {
        $this->insumoxTarea = $insumoxTarea;

        return $this;
    }

    /**
     * Get insumoxTarea
     *
     * @return \AppBundle\Entity\InsumoxTarea 
     */
    public function getInsumoxTarea()
    {
        return $this->insumoxTarea;
    }
}
