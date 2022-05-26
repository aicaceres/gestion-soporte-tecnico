<?php

namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * AppBundle\Entity\Compra
 * @ORM\Table(name="compra")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\CompraRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 */
class Compra {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var datetime $fechaCompra
     * @ORM\Column(name="fecha_compra", type="datetime", nullable=false)
     */
    private $fechaCompra;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Ubicacion")
     * @ORM\JoinColumn(name="razon_social_id", referencedColumnName="id")
     */
    protected $razonSocial;

    /**
     * @var string $nroFactura
     * @ORM\Column(name="nro_factura", type="string",length=20, nullable=true)
     */
    protected $nroFactura;

    /**
     * @var string $nroRemito
     * @ORM\Column(name="nro_remito", type="string", length=20,nullable=true)
     */
    protected $nroRemito;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento")
     * @ORM\JoinColumn(name="solicitante_id", referencedColumnName="id")
     */
    protected $solicitante;

    /**
     * @var string $ordenCompra
     * @ORM\Column(name="orden_compra", type="string", length=20,nullable=false)
     */
    protected $ordenCompra;

    /**
     * @var string $nroCuenta
     * @ORM\Column(name="nro_cuenta", type="string", length=20,nullable=true)
     */
    protected $nroCuenta;

    /**
     * @var string $cotizacionDolar
     * @ORM\Column(name="cotizacion_dolar", type="decimal", scale=2, nullable=true)
     */
    protected $cotizacionDolar = 0;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Proveedor")
     * @ORM\JoinColumn(name="proveedor_id", referencedColumnName="id")
     */
    protected $proveedor;

    /**
     * @var string $descripcion
     * @ORM\Column(name="descripcion", type="string", nullable=true)
     */
    protected $descripcion;

    /**
     * @var integer $total
     * @ORM\Column(name="total", type="decimal", scale=2,nullable=true )
     */
    protected $total;

    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     */
    /* NUEVO - ENVIADO - RECEPCION PARCIAL - RECIBIDO - ANULADO */
    protected $estado = 'NUEVO';

    /**
     * @var datetime $fechaEnvioProveedor
     * @ORM\Column(name="fecha_envio_proveedor", type="datetime", nullable=true)
     */
    private $fechaEnvioProveedor;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\CompraDetalle", mappedBy="compra",cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $detalles;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\RecepcionCompra", mappedBy="compra")
     */
    protected $recepciones;

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

    public function getNroOc() {
        $txt = $this->getOrdenCompra();
        if ($this->getRazonSocial()) {
            $txt = $this->getRazonSocial() . '/' . $txt;
        }
        return $txt;
    }

    public function isCompleto() {
        foreach ($this->getDetalles() as $det) {
            if ($det->isPendiente())
                return false;
        }
        return true;
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
     * Set fechaCompra
     *
     * @param \DateTime $fechaCompra
     * @return Compra
     */
    public function setFechaCompra($fechaCompra) {
        $this->fechaCompra = $fechaCompra;

        return $this;
    }

    /**
     * Get fechaCompra
     *
     * @return \DateTime
     */
    public function getFechaCompra() {
        return $this->fechaCompra;
    }

    /**
     * Set nroFactura
     *
     * @param string $nroFactura
     * @return Compra
     */
    public function setNroFactura($nroFactura) {
        $this->nroFactura = $nroFactura;

        return $this;
    }

    /**
     * Get nroFactura
     *
     * @return string
     */
    public function getNroFactura() {
        return $this->nroFactura;
    }

    /**
     * Set nroRemito
     *
     * @param string $nroRemito
     * @return Compra
     */
    public function setNroRemito($nroRemito) {
        $this->nroRemito = $nroRemito;

        return $this;
    }

    /**
     * Get nroRemito
     *
     * @return string
     */
    public function getNroRemito() {
        return $this->nroRemito;
    }

    /**
     * Set ordenCompra
     *
     * @param string $ordenCompra
     * @return Compra
     */
    public function setOrdenCompra($ordenCompra) {
        $this->ordenCompra = $ordenCompra;

        return $this;
    }

    /**
     * Get ordenCompra
     *
     * @return string
     */
    public function getOrdenCompra() {
        return $this->ordenCompra;
    }

    /**
     * Set descripcion
     *
     * @param string $descripcion
     * @return Compra
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
     * Set total
     *
     * @param string $total
     * @return Compra
     */
    public function setTotal($total) {
        $this->total = $total;

        return $this;
    }

    /**
     * Get total
     *
     * @return string
     */
    public function getTotal() {
        $total = 0;
        foreach ($this->getDetalles() as $det) {
            $total = $total + ($det->getCantidad() * $det->getPrecio() );
        }
        return $total;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return Compra
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
     * @return Compra
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
     * @return Compra
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
     * @return Compra
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
     * Set solicitante
     *
     * @param \ConfigBundle\Entity\Departamento $solicitante
     * @return Compra
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
     * Set proveedor
     *
     * @param \AppBundle\Entity\Proveedor $proveedor
     * @return Compra
     */
    public function setProveedor(\AppBundle\Entity\Proveedor $proveedor = null) {
        $this->proveedor = $proveedor;

        return $this;
    }

    /**
     * Get proveedor
     *
     * @return \AppBundle\Entity\Proveedor
     */
    public function getProveedor() {
        return $this->proveedor;
    }

    /**
     * Add detalles
     *
     * @param \AppBundle\Entity\CompraDetalle $detalles
     * @return Compra
     */
    public function addDetalle(\AppBundle\Entity\CompraDetalle $detalles) {
        $detalles->setCompra($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \AppBundle\Entity\CompraDetalle $detalles
     */
    public function removeDetalle(\AppBundle\Entity\CompraDetalle $detalles) {
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
     * @return Compra
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
     * @return Compra
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
     * Set razonSocial
     *
     * @param \ConfigBundle\Entity\Ubicacion $razonSocial
     * @return Compra
     */
    public function setRazonSocial(\ConfigBundle\Entity\Ubicacion $razonSocial = null) {
        $this->razonSocial = $razonSocial;

        return $this;
    }

    /**
     * Get razonSocial
     *
     * @return \ConfigBundle\Entity\Ubicacion
     */
    public function getRazonSocial() {
        return $this->razonSocial;
    }

    /**
     * Add recepciones
     *
     * @param \AppBundle\Entity\RecepcionCompra $recepciones
     * @return Compra
     */
    public function addRecepcion(\AppBundle\Entity\RecepcionCompra $recepciones) {
        $recepciones->setCompra($this);
        $this->recepciones[] = $recepciones;
        return $this;
    }

    /**
     * Remove recepciones
     *
     * @param \AppBundle\Entity\RecepcionCompra $recepciones
     */
    public function removeRecepcion(\AppBundle\Entity\RecepcionCompra $recepciones) {
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
     * Set fechaEnvioProveedor
     *
     * @param \DateTime $fechaEnvioProveedor
     * @return Compra
     */
    public function setFechaEnvioProveedor($fechaEnvioProveedor) {
        $this->fechaEnvioProveedor = $fechaEnvioProveedor;

        return $this;
    }

    /**
     * Get fechaEnvioProveedor
     *
     * @return \DateTime
     */
    public function getFechaEnvioProveedor() {
        return $this->fechaEnvioProveedor;
    }

    public function getNroRemitoTxt() {
        $nro = '';
        // como no se está utilizando se cargan los nros de las entregas
        foreach ($this->getRecepciones() as $rec) {
            $rec_nro = trim($rec->getNroRemito());
            $nro = ( $nro == '') ? $rec_nro : $nro . ' <br> ' . $rec_nro;
        }
        return $nro;
    }

    /**
     * Set nroCuenta
     *
     * @param string $nroCuenta
     * @return Compra
     */
    public function setNroCuenta($nroCuenta) {
        $this->nroCuenta = $nroCuenta;

        return $this;
    }

    /**
     * Get nroCuenta
     *
     * @return string
     */
    public function getNroCuenta() {
        return $this->nroCuenta;
    }

    /**
     * Set cotizacionDolar
     *
     * @param string $cotizacionDolar
     * @return Compra
     */
    public function setCotizacionDolar($cotizacionDolar) {
        $this->cotizacionDolar = $cotizacionDolar;

        return $this;
    }

    /**
     * Get cotizacionDolar
     *
     * @return string
     */
    public function getCotizacionDolar() {
        return $this->cotizacionDolar;
    }

}