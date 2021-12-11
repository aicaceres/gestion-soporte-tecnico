<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
/**
 * ConfigBundle\Entity\Piso
 * @ORM\Table(name="piso")
 * @ORM\Entity()
 */
class Piso
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
     */
    protected $nombre;

    /**
     * @ORM\ManyToMany(targetEntity="ConfigBundle\Entity\Departamento", mappedBy="pisos")
     */
    protected $departamentos;             
        
    public function __toString() {
        return $this->nombre;
    }    
    
    /**
     * Get id
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nombre
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
        $this->departamentos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add departamentos
     *
     * @param \ConfigBundle\Entity\Departamento $departamentos
     * @return Piso
     */
    public function addDepartamento(\ConfigBundle\Entity\Departamento $departamentos)
    {
        $this->departamentos[] = $departamentos;

        return $this;
    }

    /**
     * Remove departamentos
     *
     * @param \ConfigBundle\Entity\Departamento $departamentos
     */
    public function removeDepartamento(\ConfigBundle\Entity\Departamento $departamentos)
    {
        $this->departamentos->removeElement($departamentos);
    }

    /**
     * Get departamentos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDepartamentos()
    {
        return $this->departamentos;
    }
}
