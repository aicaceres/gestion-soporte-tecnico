<?php
namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * AppBundle\Entity\Vencimiento
 * @ORM\Table(name="vencimiento")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\VencimientoRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)  
 */
class Vencimiento
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\TipoVencimiento",inversedBy="vencimientos")
     * @ORM\JoinColumn(name="tipo_vencimiento_id", referencedColumnName="id")    
     */
    protected $tipo;     
    /**
     * @var string $detalle
     * @ORM\Column(name="detalle", type="string", nullable=false)     
     */
    protected $detalle;    
    /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\Proveedor")
     *@ORM\JoinColumn(name="proveedor_id", referencedColumnName="id")      
     */
    protected $proveedor;    
    /**
     * @var string $ordenCompra
     * @ORM\Column(name="orden_compra", type="string", nullable=true)     
     */
    protected $ordenCompra;    
    
    /**
     * @var datetime $fechaInicio
     * @ORM\Column(name="fecha_inicio", type="date", nullable=false)
     */
    private $fechaInicio;    
    /**
     * @var datetime $fechaFin
     * @ORM\Column(name="fecha_fin", type="date", nullable=false)
     */
    private $fechaFin;    
    
    /**
     * @var string $periodo
     * @ORM\Column(name="periodo", type="string", nullable=true)     
     */
    protected $periodo;    
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Moneda")
     * @ORM\JoinColumn(name="moneda_id", referencedColumnName="id")
     */
    protected $moneda;
    
    /**
     * @var string $abono
     * @ORM\Column(name="abono", type="string", nullable=true)     
     */
    protected $abono;
    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones; 

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
       

    public function __toString() {
        return $this->getDetalle();
    }    

    public function getEstado(){
        $hoy = new \DateTime();
        $mes = date("Ymd",strtotime($hoy->format('Ymd')."+ 30 days"));
        $fin = $this->getFechaFin()->format('Ymd');
        if( $fin > $mes ){
            return 'success';
        }elseif( $fin < $hoy->format('Ymd') ){
            return 'danger';
        }elseif( $fin <= $mes ){
            return 'warning';
        }
    }
    public function getEstadoTxt(){
        $hoy = new \DateTime();
        $mes = date("Ymd",strtotime($hoy->format('Ymd')."+ 30 days"));
        $fin = $this->getFechaFin()->format('Ymd');
        if( $fin > $mes ){
            return 'En término';
        }elseif( $fin < $hoy->format('Ymd') ){
            return 'Vencido';
        }elseif( $fin <= $mes ){
            return 'Por Vencer';
        }
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set detalle
     *
     * @param string $detalle
     * @return Vencimiento
     */
    public function setDetalle($detalle)
    {
        $this->detalle = $detalle;

        return $this;
    }

    /**
     * Get detalle
     *
     * @return string 
     */
    public function getDetalle()
    {
        return $this->detalle;
    }

    /**
     * Set ordenCompra
     *
     * @param string $ordenCompra
     * @return Vencimiento
     */
    public function setOrdenCompra($ordenCompra)
    {
        $this->ordenCompra = $ordenCompra;

        return $this;
    }

    /**
     * Get ordenCompra
     *
     * @return string 
     */
    public function getOrdenCompra()
    {
        return $this->ordenCompra;
    }

    /**
     * Set fechaInicio
     *
     * @param \DateTime $fechaInicio
     * @return Vencimiento
     */
    public function setFechaInicio($fechaInicio)
    {
        $this->fechaInicio = $fechaInicio;

        return $this;
    }

    /**
     * Get fechaInicio
     *
     * @return \DateTime 
     */
    public function getFechaInicio()
    {
        return $this->fechaInicio;
    }

    /**
     * Set fechaFin
     *
     * @param \DateTime $fechaFin
     * @return Vencimiento
     */
    public function setFechaFin($fechaFin)
    {
        $this->fechaFin = $fechaFin;

        return $this;
    }

    /**
     * Get fechaFin
     *
     * @return \DateTime 
     */
    public function getFechaFin()
    {
        return $this->fechaFin;
    }

    /**
     * Set periodo
     *
     * @param string $periodo
     * @return Vencimiento
     */
    public function setPeriodo($periodo)
    {
        $this->periodo = $periodo;

        return $this;
    }

    /**
     * Get periodo
     *
     * @return string 
     */
    public function getPeriodo()
    {
        return $this->periodo;
    }

    /**
     * Set abono
     *
     * @param string $abono
     * @return Vencimiento
     */
    public function setAbono($abono)
    {
        $this->abono = $abono;

        return $this;
    }

    /**
     * Get abono
     *
     * @return string 
     */
    public function getAbono()
    {
        return $this->abono;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return Vencimiento
     */
    public function setObservaciones($observaciones)
    {
        $this->observaciones = $observaciones;

        return $this;
    }

    /**
     * Get observaciones
     *
     * @return string 
     */
    public function getObservaciones()
    {
        return $this->observaciones;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Vencimiento
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Vencimiento
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return Vencimiento
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime 
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set tipo
     *
     * @param \ConfigBundle\Entity\TipoVencimiento $tipo
     * @return Vencimiento
     */
    public function setTipo(\ConfigBundle\Entity\TipoVencimiento $tipo = null)
    {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return \ConfigBundle\Entity\TipoVencimiento 
     */
    public function getTipo()
    {
        return $this->tipo;
    }

    /**
     * Set proveedor
     *
     * @param \AppBundle\Entity\Proveedor $proveedor
     * @return Vencimiento
     */
    public function setProveedor(\AppBundle\Entity\Proveedor $proveedor = null)
    {
        $this->proveedor = $proveedor;

        return $this;
    }

    /**
     * Get proveedor
     *
     * @return \AppBundle\Entity\Proveedor 
     */
    public function getProveedor()
    {
        return $this->proveedor;
    }

    /**
     * Set moneda
     *
     * @param \ConfigBundle\Entity\Moneda $moneda
     * @return Vencimiento
     */
    public function setMoneda(\ConfigBundle\Entity\Moneda $moneda = null)
    {
        $this->moneda = $moneda;

        return $this;
    }

    /**
     * Get moneda
     *
     * @return \ConfigBundle\Entity\Moneda 
     */
    public function getMoneda()
    {
        return $this->moneda;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Vencimiento
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario 
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return Vencimiento
     */
    public function setUpdatedBy(\ConfigBundle\Entity\Usuario $updatedBy = null)
    {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ConfigBundle\Entity\Usuario 
     */
    public function getUpdatedBy()
    {
        return $this->updatedBy;
    }
}
