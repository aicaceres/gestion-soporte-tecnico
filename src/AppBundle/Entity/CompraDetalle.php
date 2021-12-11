<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\CompraDetalle
 * @ORM\Table(name="compra_detalle")
 * @ORM\Entity()
 */
class CompraDetalle {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Insumo",cascade={"persist"})
     * @ORM\JoinColumn(name="insumo_id", referencedColumnName="id")
     */
    protected $insumo;

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
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=true)
     */
    protected $nombre;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Tipo")
     * @ORM\JoinColumn(name="tipo_id", referencedColumnName="id")
     */
    protected $tipo;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Marca")
     * @ORM\JoinColumn(name="item_marca_id", referencedColumnName="id")
     */
    protected $itemMarca;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Modelo")
     * @ORM\JoinColumn(name="item_modelo_id", referencedColumnName="id")
     */
    protected $itemModelo;

    /**
     * @var integer $recibido
     * @ORM\Column(name="recibido", type="decimal", scale=2)
     */
    protected $recibido = 0;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Compra", inversedBy="detalles")
     * @ORM\JoinColumn(name="compra_id", referencedColumnName="id")
     */
    protected $compra;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\RecepcionCompraDetalle", mappedBy="compraDetalle")
     */
    protected $recepciones;


// En desuso ---------------

    /**
     * @var integer $codigo
     * @ORM\Column(name="codigo", type="string", nullable=true)
     */
    protected $codigo;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Equipo")
     * @ORM\JoinColumn(name="equipo_id", referencedColumnName="id")
     */
    protected $equipo;

    /**
     * @var string $nroSerie
     * @ORM\Column(name="nro_serie", type="string", nullable=true)
     */
    protected $nroSerie;

    public function getRecepcionesArray() {
        $recepciones = array();
        foreach ($this->getRecepciones() as $recep) {
            array_push($recepciones, array('fecha' => $recep->getRecepcion()->getFechaRecepcion()->format('d-m-Y'), 'cantidad' => $recep->getCantidad()));
        }
        return $recepciones;
    }

    /**
     * Set codigo
     * @param integer $codigo
     * @return CompraDetalle
     */
    public function setCodigo($codigo) {
        $this->codigo = $codigo;
        return $this;
    }

    /**
     * Get codigo
     * @return integer
     */
    public function getCodigo() {
        return $this->codigo;
    }

    /**
     * Set nroSerie
     * @param string $nroSerie
     * @return CompraDetalle
     */
    public function setNroSerie($nroSerie) {
        $this->nroSerie = $nroSerie;
        return $this;
    }

    /**
     * Get nroSerie
     * @return string
     */
    public function getNroSerie() {
        return $this->nroSerie;
    }

    /**
     * Set equipo
     * @param \AppBundle\Entity\Equipo $equipo
     * @return CompraDetalle
     */
    public function setEquipo(\AppBundle\Entity\Equipo $equipo = null) {
        $this->equipo = $equipo;
        return $this;
    }

    /**
     * Get equipo
     * @return \AppBundle\Entity\Equipo
     */
    public function getEquipo() {
        return $this->equipo;
    }

// ----------------------------

    public function getClaseDetalle() {
        if ($this->getInsumo()) {
            return $this->getInsumo()->getTipo()->getClase();
        }
        elseif ($this->getEquipo()) {
            return $this->getEquipo()->getTipo()->getClase();
        }
        else {
            return $this->getTipo()->getClase();
        }
    }

    public function __toString() {
        return $this->getCodigoItem() . ' | ' . $this->getDescripcion();
    }

    public function isEquipo() {
        if ($this->getTipo()) {
            return ($this->getTipo()->getClase() == 'E' ) ? true : false;
        }
        return false;
    }

    public function isPendiente() {
        return ( $this->getRecibido() < $this->getCantidad() ) ? true : false;
    }

    public function getCantidadPendiente() {
        return ( $this->isPendiente() ) ? ($this->getCantidad() - $this->getRecibido()) : 0;
    }

    public function getDescripcion() {
        if ($this->getInsumo()) {
            return $this->getInsumo()->getTexto();
        }
        else if ($this->getEquipo()) {
            $eq = $this->getEquipo();
            return $eq->getTipo()->getNombre() . ' - ' . $eq->getNombre() . ' - ' . $eq->getMarca()->getNombre() . ' - ' .
                    $eq->getModelo()->getNombre() . ' - ' . $eq->getNroSerie();
        }
        else {
            return $this->getTipo()->getNombre() . ' - ' . $this->getNombre() . ' - ' . $this->getItemMarca()->getNombre() . ' - ' . $this->getItemModelo()->getNombre();
        }
    }

    public function getCodigoItem() {
        if ($this->getInsumo()) {
            return $this->getInsumo()->getCodigoItem();
        }
        else if ($this->getEquipo()) {
            return $this->getEquipo()->getCodigoItem();
        }
        else {
            return $this->getCodigo();
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
     * @return CompraDetalle
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
     * Set precio
     *
     * @param string $precio
     * @return CompraDetalle
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
     * Set nombre
     *
     * @param string $nombre
     * @return CompraDetalle
     */
    public function setNombre($nombre) {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre() {
        return $this->nombre;
    }

    /**
     * Set recibido
     *
     * @param string $recibido
     * @return CompraDetalle
     */
    public function setRecibido($recibido) {
        $this->recibido = $recibido;

        return $this;
    }

    /**
     * Get recibido
     *
     * @return string
     */
    public function getRecibido() {
        return $this->recibido;
    }

    /**
     * Set insumo
     *
     * @param \AppBundle\Entity\Insumo $insumo
     * @return CompraDetalle
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
     * Set tipo
     *
     * @param \ConfigBundle\Entity\Tipo $tipo
     * @return CompraDetalle
     */
    public function setTipo(\ConfigBundle\Entity\Tipo $tipo = null) {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return \ConfigBundle\Entity\Tipo
     */
    public function getTipo() {
        return $this->tipo;
    }

    /**
     * Set itemMarca
     *
     * @param \ConfigBundle\Entity\Marca $itemMarca
     * @return CompraDetalle
     */
    public function setItemMarca(\ConfigBundle\Entity\Marca $itemMarca = null) {
        $this->itemMarca = $itemMarca;

        return $this;
    }

    /**
     * Get itemMarca
     *
     * @return \ConfigBundle\Entity\Marca
     */
    public function getItemMarca() {
        return $this->itemMarca;
    }

    /**
     * Set itemModelo
     *
     * @param \ConfigBundle\Entity\Modelo $itemModelo
     * @return CompraDetalle
     */
    public function setItemModelo(\ConfigBundle\Entity\Modelo $itemModelo = null) {
        $this->itemModelo = $itemModelo;

        return $this;
    }

    /**
     * Get itemModelo
     *
     * @return \ConfigBundle\Entity\Modelo
     */
    public function getItemModelo() {
        return $this->itemModelo;
    }

    /**
     * Set compra
     *
     * @param \AppBundle\Entity\Compra $compra
     * @return CompraDetalle
     */
    public function setCompra(\AppBundle\Entity\Compra $compra = null) {
        $this->compra = $compra;

        return $this;
    }

    /**
     * Get compra
     *
     * @return \AppBundle\Entity\Compra
     */
    public function getCompra() {
        return $this->compra;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->recepciones = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add recepciones
     *
     * @param \AppBundle\Entity\RecepcionCompraDetalle $recepciones
     * @return CompraDetalle
     */
    public function addRecepcion(\AppBundle\Entity\RecepcionCompraDetalle $recepciones) {
        $this->recepciones[] = $recepciones;

        return $this;
    }

    /**
     * Remove recepciones
     *
     * @param \AppBundle\Entity\RecepcionCompraDetalle $recepciones
     */
    public function removeRecepcion(\AppBundle\Entity\RecepcionCompraDetalle $recepciones) {
        $this->recepciones->removeElement($recepciones);
    }

    /**
     * Get recepciones
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRecepciones() {
        return $this->recepciones;
    }

    /**
     * Set moneda
     *
     * @param \ConfigBundle\Entity\Moneda $moneda
     * @return CompraDetalle
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

    /**
     * Add recepciones
     *
     * @param \AppBundle\Entity\RecepcionCompraDetalle $recepciones
     * @return CompraDetalle
     */
    public function addRecepcione(\AppBundle\Entity\RecepcionCompraDetalle $recepciones) {
        $this->recepciones[] = $recepciones;

        return $this;
    }

    /**
     * Remove recepciones
     *
     * @param \AppBundle\Entity\RecepcionCompraDetalle $recepciones
     */
    public function removeRecepcione(\AppBundle\Entity\RecepcionCompraDetalle $recepciones) {
        $this->recepciones->removeElement($recepciones);
    }

}