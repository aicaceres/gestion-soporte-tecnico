<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * AppBundle\Entity\StockMovimiento
 *
 * @ORM\Table(name="stock_movimiento")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\StockRepository")
 */
class StockMovimiento
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @var date $fecha
     * @ORM\Column(name="fecha", type="date", nullable=false)
     */
    private $fecha;     
    
    /**
     * @ORM\Column(name="observaciones", type="text", nullable=true)
     */
    protected $observaciones;
    
     /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento")
     * @ORM\JoinColumn(name="deposito_origen_id", referencedColumnName="id")
     */
    protected $depositoOrigen; 
     /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento")
     * @ORM\JoinColumn(name="deposito_destino_id", referencedColumnName="id")
     */
    protected $depositoDestino; 

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\StockMovimientoDetalle", mappedBy="stockMovimiento",cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $detalles;
    
    /**
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;
    
    /**
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return StockMovimiento
     */
    public function setFecha($fecha)
    {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime 
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * Set observaciones
     *
     * @param string $observaciones
     * @return StockMovimiento
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
     * @return StockMovimiento
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
     * Set depositoOrigen
     *
     * @param \ConfigBundle\Entity\Departamento $depositoOrigen
     * @return StockMovimiento
     */
    public function setDepositoOrigen(\ConfigBundle\Entity\Departamento $depositoOrigen = null)
    {
        $this->depositoOrigen = $depositoOrigen;

        return $this;
    }

    /**
     * Get depositoOrigen
     *
     * @return \ConfigBundle\Entity\Departamento 
     */
    public function getDepositoOrigen()
    {
        return $this->depositoOrigen;
    }

    /**
     * Set depositoDestino
     *
     * @param \ConfigBundle\Entity\Departamento $depositoDestino
     * @return StockMovimiento
     */
    public function setDepositoDestino(\ConfigBundle\Entity\Departamento $depositoDestino = null)
    {
        $this->depositoDestino = $depositoDestino;

        return $this;
    }

    /**
     * Get depositoDestino
     *
     * @return \ConfigBundle\Entity\Departamento 
     */
    public function getDepositoDestino()
    {
        return $this->depositoDestino;
    }

    /**
     * Add detalles
     *
     * @param \AppBundle\Entity\StockMovimientoDetalle $detalles
     * @return StockMovimiento
     */
    public function addDetalle(\AppBundle\Entity\StockMovimientoDetalle $detalles)
    {
        $detalles->setStockMovimiento($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \AppBundle\Entity\StockMovimientoDetalle $detalles
     */
    public function removeDetalle(\AppBundle\Entity\StockMovimientoDetalle $detalles)
    {
        $this->detalles->removeElement($detalles);
    }

    /**
     * Get detalles
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDetalles()
    {
        return $this->detalles;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return StockMovimiento
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
}
