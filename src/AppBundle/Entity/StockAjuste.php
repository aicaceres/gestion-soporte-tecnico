<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * AppBundle\Entity\StockAjuste
 *
 * @ORM\Table(name="stock_ajuste")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\StockRepository")
 */
class StockAjuste
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
     * @ORM\JoinColumn(name="deposito_id", referencedColumnName="id")
     */
    protected $deposito; 

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\StockAjusteDetalle", mappedBy="stockAjuste",cascade={"persist", "remove"})
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
     * @return StockAjuste
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
     * @return StockAjuste
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
     * @return StockAjuste
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
     * Set deposito
     *
     * @param \ConfigBundle\Entity\Departamento $deposito
     * @return StockAjuste
     */
    public function setDeposito(\ConfigBundle\Entity\Departamento $deposito = null)
    {
        $this->deposito = $deposito;

        return $this;
    }

    /**
     * Get deposito
     *
     * @return \ConfigBundle\Entity\Departamento 
     */
    public function getDeposito()
    {
        return $this->deposito;
    }

    /**
     * Add detalles
     *
     * @param \AppBundle\Entity\StockAjusteDetalle $detalles
     * @return StockAjuste
     */
    public function addDetalle(\AppBundle\Entity\StockAjusteDetalle $detalles)
    {
        $detalles->setStockAjuste($this);
        $this->detalles[] = $detalles;
        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \AppBundle\Entity\StockAjusteDetalle $detalles
     */
    public function removeDetalle(\AppBundle\Entity\StockAjusteDetalle $detalles)
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
     * @return StockAjuste
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
