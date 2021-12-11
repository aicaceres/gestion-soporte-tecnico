<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * ConfigBundle\Entity\TipoVencimiento
 * @ORM\Table(name="tipo_vencimiento")
 * @ORM\Entity()
 */
class TipoVencimiento
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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Vencimiento", mappedBy="tipo")
    */
    protected $vencimientos;    

    public function __toString() {
        return $this->nombre;
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
     * @return TipoTarea
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
     * Constructor
     */
    public function __construct()
    {
        $this->vencimientos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add vencimientos
     *
     * @param \AppBundle\Entity\Vencimiento $vencimientos
     * @return TipoVencimiento
     */
    public function addVencimiento(\AppBundle\Entity\Vencimiento $vencimientos)
    {
        $this->vencimientos[] = $vencimientos;

        return $this;
    }

    /**
     * Remove vencimientos
     *
     * @param \AppBundle\Entity\Vencimiento $vencimientos
     */
    public function removeVencimiento(\AppBundle\Entity\Vencimiento $vencimientos)
    {
        $this->vencimientos->removeElement($vencimientos);
    }

    /**
     * Get vencimientos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getVencimientos()
    {
        return $this->vencimientos;
    }
}
