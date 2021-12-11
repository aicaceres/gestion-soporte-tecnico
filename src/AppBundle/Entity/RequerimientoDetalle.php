<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\RequerimientoDetalle
 * @ORM\Table(name="requerimiento_detalle")
 * @ORM\Entity()
 * @Gedmo\Loggable()  
 */
class RequerimientoDetalle
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
     /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\Equipo", inversedBy="requerimientos")
     *@ORM\JoinColumn(name="equipo_id", referencedColumnName="id") 
     * @Gedmo\Versioned()
     */
    protected $equipo;    
    /**
     * @var string $descripcion
     * @ORM\Column(name="descripcion", type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $descripcion;  

     /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\Requerimiento", inversedBy="detalles",cascade={"persist"})
     *@ORM\JoinColumn(name="requerimiento_id", referencedColumnName="id") 
     */
    protected $requerimiento;  
    
    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Estado")
     * @ORM\JoinColumn(name="estado_original_id", referencedColumnName="id")
     */
    protected $estadoOriginal;       
     /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\EquipoUbicacion")
     * @ORM\JoinColumn(name="equipo_ubicacion_original_id", referencedColumnName="id")
     */
    protected $equipoUbicacionOriginal;       
    
    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\OrdenTrabajoDetalle", mappedBy="requerimientoDetalle")
     */    
    protected $ordenTrabajoDetalle;    
    
    /**
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\EquipoUbicacion", mappedBy="requerimientoDetalle")
     */    
    private $equipoUbicacionRequerimiento;
    
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
        return strval($this->getId());
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
     * Set descripcion
     *
     * @param string $descripcion
     * @return RequerimientoDetalle
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
     * Set created
     *
     * @param \DateTime $created
     * @return RequerimientoDetalle
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
     * @return RequerimientoDetalle
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
     * Set equipo
     *
     * @param \AppBundle\Entity\Equipo $equipo
     * @return RequerimientoDetalle
     */
    public function setEquipo(\AppBundle\Entity\Equipo $equipo = null)
    {
        $this->equipo = $equipo;

        return $this;
    }

    /**
     * Get equipo
     *
     * @return \AppBundle\Entity\Equipo 
     */
    public function getEquipo()
    {
        return $this->equipo;
    }

    /**
     * Set requerimiento
     *
     * @param \AppBundle\Entity\Requerimiento $requerimiento
     * @return RequerimientoDetalle
     */
    public function setRequerimiento(\AppBundle\Entity\Requerimiento $requerimiento = null)
    {
        $this->requerimiento = $requerimiento;

        return $this;
    }

    /**
     * Get requerimiento
     *
     * @return \AppBundle\Entity\Requerimiento 
     */
    public function getRequerimiento()
    {
        return $this->requerimiento;
    }

    /**
     * Set estadoOriginal
     *
     * @param \ConfigBundle\Entity\Estado $estadoOriginal
     * @return RequerimientoDetalle
     */
    public function setEstadoOriginal(\ConfigBundle\Entity\Estado $estadoOriginal = null)
    {
        $this->estadoOriginal = $estadoOriginal;

        return $this;
    }

    /**
     * Get estadoOriginal
     *
     * @return \ConfigBundle\Entity\Estado 
     */
    public function getEstadoOriginal()
    {
        return $this->estadoOriginal;
    }

    /**
     * Set equipoUbicacionOriginal
     *
     * @param \AppBundle\Entity\EquipoUbicacion $equipoUbicacionOriginal
     * @return RequerimientoDetalle
     */
    public function setEquipoUbicacionOriginal(\AppBundle\Entity\EquipoUbicacion $equipoUbicacionOriginal = null)
    {
        $this->equipoUbicacionOriginal = $equipoUbicacionOriginal;

        return $this;
    }

    /**
     * Get equipoUbicacionOriginal
     *
     * @return \AppBundle\Entity\EquipoUbicacion 
     */
    public function getEquipoUbicacionOriginal()
    {
        return $this->equipoUbicacionOriginal;
    }

    /**
     * Set ordenTrabajoDetalle
     *
     * @param \AppBundle\Entity\OrdenTrabajoDetalle $ordenTrabajoDetalle
     * @return RequerimientoDetalle
     */
    public function setOrdenTrabajoDetalle(\AppBundle\Entity\OrdenTrabajoDetalle $ordenTrabajoDetalle = null)
    {
        $this->ordenTrabajoDetalle = $ordenTrabajoDetalle;

        return $this;
    }

    /**
     * Get ordenTrabajoDetalle
     *
     * @return \AppBundle\Entity\OrdenTrabajoDetalle 
     */
    public function getOrdenTrabajoDetalle()
    {
        return $this->ordenTrabajoDetalle;
    }

    /**
     * Set equipoUbicacionRequerimiento
     *
     * @param \AppBundle\Entity\EquipoUbicacion $equipoUbicacionRequerimiento
     * @return RequerimientoDetalle
     */
    public function setEquipoUbicacionRequerimiento(\AppBundle\Entity\EquipoUbicacion $equipoUbicacionRequerimiento = null)
    {
        $this->equipoUbicacionRequerimiento = $equipoUbicacionRequerimiento;

        return $this;
    }

    /**
     * Get equipoUbicacionRequerimiento
     *
     * @return \AppBundle\Entity\EquipoUbicacion 
     */
    public function getEquipoUbicacionRequerimiento()
    {
        return $this->equipoUbicacionRequerimiento;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return RequerimientoDetalle
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
     * @return RequerimientoDetalle
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
