<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * ConfigBundle\Entity\Tipo
 *
 * @ORM\Table(name="tipo",uniqueConstraints={@ORM\UniqueConstraint(name="tipoequipoinsumo_idx", columns={"nombre", "clase"})})
 * @ORM\Entity(repositoryClass="ConfigBundle\Entity\ParametroRepository")
 * @UniqueEntity(
 *     fields={"nombre", "clase"}, errorPath="nombre",
 *     message="Este tipo ya existe en esta clase."
 * )
 */
class Tipo
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=false)
     * @Assert\NotBlank()
     */
    protected $nombre;
    /**
     * @var string $clase
     * @ORM\Column(name="clase", type="string", length=1, nullable=false)
     */
    protected $clase;
    
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Insumo", mappedBy="tipo")
    */
    protected $insumos;
    

    public function __toString() {
        return $this->nombre;
    }
    
    public function getInsumosEnStock(){
        foreach ($this->getInsumos() as $ins){
            if($ins->getStockTotal()>0 ){
                return false;
            }                
        }
        return true;
    }


    public function getTxtselect() {
        return $this->nombre.' ('.$this->getClase().')' ;
    } 
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->clase = 'E';
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
     * Set nombre
     *
     * @param string $nombre
     * @return Nacionalidad
     */
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string 
     */
    public function getNombre()
    {
        return $this->nombre;
    }


    /**
     * Set clase
     *
     * @param string $clase
     * @return Tipo
     */
    public function setClase($clase)
    {
        $this->clase = $clase;

        return $this;
    }

    /**
     * Get clase
     *
     * @return string 
     */
    public function getClase()
    {
        return $this->clase;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Tipo
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
     * @return Tipo
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
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Tipo
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
     * @return Tipo
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


    /**
     * Add insumos
     *
     * @param \AppBundle\Entity\Insumo $insumos
     * @return Tipo
     */
    public function addInsumo(\AppBundle\Entity\Insumo $insumos)
    {
        $this->insumos[] = $insumos;

        return $this;
    }

    /**
     * Remove insumos
     *
     * @param \AppBundle\Entity\Insumo $insumos
     */
    public function removeInsumo(\AppBundle\Entity\Insumo $insumos)
    {
        $this->insumos->removeElement($insumos);
    }

    /**
     * Get insumos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInsumos()
    {
        return $this->insumos;
    }
}
