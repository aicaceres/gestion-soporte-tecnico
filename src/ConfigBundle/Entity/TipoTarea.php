<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * ConfigBundle\Entity\TipoTarea
 * @ORM\Table(name="tipo_tarea")
 * @ORM\Entity()
 */
class TipoTarea
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
     * @var string $abreviatura
     * @ORM\Column(name="abreviatura", type="string", unique=true)
     */
    protected $abreviatura;    
    /**
     * @ORM\Column(name="inicial", type="boolean")
     */
    protected $inicial = false;    

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
     * Set abreviatura
     *
     * @param string $abreviatura
     * @return TipoTarea
     */
    public function setAbreviatura($abreviatura)
    {
        $this->abreviatura = $abreviatura;

        return $this;
    }

    /**
     * Get abreviatura
     *
     * @return string 
     */
    public function getAbreviatura()
    {
        return $this->abreviatura;
    }

    /**
     * Set inicial
     *
     * @param boolean $inicial
     * @return TipoTarea
     */
    public function setInicial($inicial)
    {
        $this->inicial = $inicial;

        return $this;
    }

    /**
     * Get inicial
     *
     * @return boolean 
     */
    public function getInicial()
    {
        return $this->inicial;
    }
}
