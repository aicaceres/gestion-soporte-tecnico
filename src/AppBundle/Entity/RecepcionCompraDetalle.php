<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\RecepcionCompraDetalle
 * @ORM\Table(name="recepcion_compra_detalle")
 * @ORM\Entity()
 */
class RecepcionCompraDetalle {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CompraDetalle", inversedBy="recepciones")
     * @ORM\JoinColumn(name="compra_detalle_id", referencedColumnName="id")
     */
    protected $compraDetalle;

    /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=2)
     */
    protected $cantidad;

    /**
     * @var integer $precio
     * @ORM\Column(name="precio", type="decimal", scale=2,nullable=true )
     */
    protected $precio;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Moneda")
     * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id")
     */
    protected $moneda;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Insumo",cascade={"persist"})
     * @ORM\JoinColumn(name="insumo_id", referencedColumnName="id")
     */
    protected $insumo;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Equipo", inversedBy="detcompra")
     * @ORM\JoinColumn(name="equipo_id", referencedColumnName="id")
     */
    protected $equipo;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\RecepcionCompra", inversedBy="detalles")
     * @ORM\JoinColumn(name="recepcion_compra_id", referencedColumnName="id")
     */
    protected $recepcion;

    public function getDescripcion() {
        $clase = $this->getCompraDetalle()->getClaseDetalle();
        if ($clase == 'I') {
            if ($this->getInsumo()) {
                return $this->getInsumo()->getTexto();
            }
            else {
                return $this->getCompraDetalle()->getDescripcion();
            }
        }
        else {
            if ($this->getEquipo()) {
                return $this->getEquipo()->getTextoCompleto();
            }
            else {
                return $this->getCompraDetalle()->getDescripcion();
            }
        }
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
     * Set cantidad
     *
     * @param string $cantidad
     * @return RecepcionCompraDetalle
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
     * Set compraDetalle
     *
     * @param \AppBundle\Entity\CompraDetalle $compraDetalle
     * @return RecepcionCompraDetalle
     */
    public function setCompraDetalle(\AppBundle\Entity\CompraDetalle $compraDetalle = null) {
        $this->compraDetalle = $compraDetalle;

        return $this;
    }

    /**
     * Get compraDetalle
     *
     * @return \AppBundle\Entity\CompraDetalle
     */
    public function getCompraDetalle() {
        return $this->compraDetalle;
    }

    /**
     * Set recepcion
     *
     * @param \AppBundle\Entity\RecepcionCompra $recepcion
     * @return RecepcionCompraDetalle
     */
    public function setRecepcion(\AppBundle\Entity\RecepcionCompra $recepcion = null) {
        $this->recepcion = $recepcion;

        return $this;
    }

    /**
     * Get recepcion
     *
     * @return \AppBundle\Entity\RecepcionCompra
     */
    public function getRecepcion() {
        return $this->recepcion;
    }

    /**
     * Set precio
     *
     * @param string $precio
     * @return RecepcionCompraDetalle
     */
    public function setPrecio($precio) {
        $this->precio = $precio;

        return $this;
    }

    /**
     * Get precio
     *
     * @return string
     */
    public function getPrecio() {
        return $this->precio;
    }

    /**
     * Set insumo
     *
     * @param \AppBundle\Entity\Insumo $insumo
     * @return RecepcionCompraDetalle
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
     * Set equipo
     *
     * @param \AppBundle\Entity\Equipo $equipo
     * @return RecepcionCompraDetalle
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
     * Set moneda
     *
     * @param \ConfigBundle\Entity\Moneda $moneda
     * @return RecepcionCompraDetalle
     */
    public function setMoneda(\ConfigBundle\Entity\Moneda $moneda = null) {
        $this->moneda = $moneda;

        return $this;
    }

    /**
     * Get moneda
     *
     * @return \ConfigBundle\Entity\Moneda
     */
    public function getMoneda() {
        return $this->moneda;
    }

}