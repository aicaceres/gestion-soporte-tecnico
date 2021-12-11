<?php
namespace ConfigBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * ConfigBundle\Entity\Rol
 * @ORM\Table(name="rol",uniqueConstraints={@ORM\UniqueConstraint(name="rol_idx", columns={"nombre"})})
 * @ORM\Entity() 
 * @UniqueEntity(
 *     fields={"nombre"}, errorPath="nombre", message="Este nombre de perfil ya existe."
 * )
 */
class Rol
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", length=255)
     */
    protected $nombre='ROLE_';  
    
    /**
     * @var string $descripcion
     * @ORM\Column(name="descripcion", type="string")
     */
    protected $descripcion;    
    
    /**
     * @var string $initRoute
     * @ORM\Column(name="init_route", type="string")
     */
    protected $initRoute="homepage";    

    /**
     * @ORM\Column(name="activo", type="boolean")
     */
    protected $activo = true; 
    
    /**
     * @ORM\Column(name="admin", type="boolean")
     */
    protected $admin = false; 
    
    /**
     * @ORM\Column(name="tecnico", type="boolean")
     */
    protected $tecnico = false; 

    /**
     * @ORM\Column(name="fijo", type="boolean")
     */
    protected $fijo = false; 
    
    /**
     * @ORM\ManyToMany(targetEntity="ConfigBundle\Entity\Permiso")
     * @ORM\JoinTable(name="pemisos_x_rol",
     *      joinColumns={@ORM\JoinColumn(name="rol_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="permiso_id", referencedColumnName="id")}
     * )
     */
    private $permisos;    
    
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
    
    public function __toString() {
        return $this->descripcion;
    }

    public function getAccess($slug){
        if($this->getAdmin()) return TRUE;
        foreach ($this->getPermisos() as $permiso) {
            if($permiso->getRoute()===$slug){
                return TRUE;                
            }
        }
        return FALSE; 
    }
    public function getPermiso($slug){
        foreach ($this->getPermisos() as $permiso) {
            if($permiso->getRoute()===$slug){
                return TRUE;                
            }
        }
        return FALSE; 
    }
    
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->permisos = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Get fijo
     *
     * @return boolean 
     */
    public function getFijo()
    {
        return $this->fijo;
    }

    /**
     * Set nombre
     *
     * @param string $nombre
     * @return Rol
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
     * Set descripcion
     *
     * @param string $descripcion
     * @return Rol
     */
    public function setDescripcion($descripcion)
    {
        $this->descripcion = $descripcion;

        return $this;
    }

    /**
     * Get descripcion
     *
     * @return string 
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * Set activo
     *
     * @param boolean $activo
     * @return Rol
     */
    public function setActivo($activo)
    {
        $this->activo = $activo;

        return $this;
    }

    /**
     * Get activo
     *
     * @return boolean 
     */
    public function getActivo()
    {
        return $this->activo;
    }

    /**
     * Add permisos
     *
     * @param \ConfigBundle\Entity\Permiso $permisos
     * @return Rol
     */
    public function addPermiso(\ConfigBundle\Entity\Permiso $permisos)
    {
        $this->permisos[] = $permisos;

        return $this;
    }

    /**
     * Remove permisos
     *
     * @param \ConfigBundle\Entity\Permiso $permisos
     */
    public function removePermiso(\ConfigBundle\Entity\Permiso $permisos)
    {
        $this->permisos->removeElement($permisos);
    }

    /**
     * Get permisos
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPermisos()
    {
        return $this->permisos;
    }

    /**
     * Set initRoute
     *
     * @param string $initRoute
     * @return Rol
     */
    public function setInitRoute($initRoute)
    {
        $this->initRoute = $initRoute;

        return $this;
    }

    /**
     * Get initRoute
     *
     * @return string 
     */
    public function getInitRoute()
    {
        return $this->initRoute;
    }

    /**
     * Set fijo
     *
     * @param boolean $fijo
     * @return Rol
     */
    public function setFijo($fijo)
    {
        $this->fijo = $fijo;

        return $this;
    }

    /**
     * Set admin
     *
     * @param boolean $admin
     * @return Rol
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;

        return $this;
    }

    /**
     * Get admin
     *
     * @return boolean 
     */
    public function getAdmin()
    {
        return $this->admin;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Rol
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
     * @return Rol
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
     * @return Rol
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
     * @return Rol
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
     * Set tecnico
     *
     * @param boolean $tecnico
     * @return Rol
     */
    public function setTecnico($tecnico)
    {
        $this->tecnico = $tecnico;

        return $this;
    }

    /**
     * Get tecnico
     *
     * @return boolean 
     */
    public function getTecnico()
    {
        return $this->tecnico;
    }
}
