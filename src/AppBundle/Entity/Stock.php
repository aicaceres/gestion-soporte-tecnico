<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * AppBundle\Entity\Stock
 *
 * @ORM\Table(name="stock",uniqueConstraints={@ORM\UniqueConstraint(name="insumoxdeposito_idx", columns={"deposito_id", "insumo_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\StockRepository")
 * @UniqueEntity(
 *     fields={"deposito", "insumo"},
 *     errorPath="insumo",
 *     message="Registro de stock duplicado."
 * )
 */
class Stock
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
     /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento")
     * @ORM\JoinColumn(name="deposito_id", referencedColumnName="id")
     */
    protected $deposito; 
    
     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Insumo", inversedBy="stock")
     * @ORM\JoinColumn(name="insumo_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $insumo; 

     /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=2 )
     */
    protected $cantidad;

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
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set cantidad
     *
     * @param string $cantidad
     * @return Stock
     */
    public function setCantidad($cantidad)
    {
        $this->cantidad = $cantidad;

        return $this;
    }

    /**
     * Get cantidad
     *
     * @return string 
     */
    public function getCantidad()
    {
        return $this->cantidad;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Stock
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
     * @return Stock
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
     * Set insumo
     *
     * @param \AppBundle\Entity\Insumo $insumo
     * @return Stock
     */
    public function setInsumo(\AppBundle\Entity\Insumo $insumo = null)
    {
        $this->insumo = $insumo;

        return $this;
    }

    /**
     * Get insumo
     *
     * @return \AppBundle\Entity\Insumo 
     */
    public function getInsumo()
    {
        return $this->insumo;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Stock
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
