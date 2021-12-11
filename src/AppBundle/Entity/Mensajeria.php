<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\Mensajeria
 * @ORM\Table(name="mensajeria")
* @ORM\Entity()
 */
class Mensajeria
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    /**
     * fecha de creacion
     * @var datetime $created
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;
    /**
     * Usuario emisor
     * @var User $createdBy
     * @Gedmo\Blameable(on="create")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     */
    private $createdBy;    
    /**
     * @var string $asunto
     * @ORM\Column(name="asunto", type="string", nullable=false)
     */
    protected $asunto;     
    /**
     * @var string $mensaje
     * @ORM\Column(name="mensaje", type="text", nullable=true)
     */    
    protected $mensaje; 
     /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario", inversedBy="mensajes" )
     * @ORM\JoinColumn(name="destinatario_id", referencedColumnName="id")
     */
    protected $destinatario;
    /**
     * @ORM\Column(name="fecha_leido", type="datetime", nullable=true)
     */
    private $fechaLeido; 

    /**
     * @var datetime $updated
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;
    /**
     * @var User $updatedBy
     * @Gedmo\Blameable(on="update")
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Usuario")
     * @ORM\JoinColumn(name="updated_by", referencedColumnName="id")
     */
    private $updatedBy;     


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
     * Set created
     *
     * @param \DateTime $created
     * @return Mensajeria
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
     * Set asunto
     *
     * @param string $asunto
     * @return Mensajeria
     */
    public function setAsunto($asunto)
    {
        $this->asunto = $asunto;

        return $this;
    }

    /**
     * Get asunto
     *
     * @return string 
     */
    public function getAsunto()
    {
        return $this->asunto;
    }

    /**
     * Set mensaje
     *
     * @param string $mensaje
     * @return Mensajeria
     */
    public function setMensaje($mensaje)
    {
        $this->mensaje = $mensaje;

        return $this;
    }

    /**
     * Get mensaje
     *
     * @return string 
     */
    public function getMensaje()
    {
        return $this->mensaje;
    }

    /**
     * Set fechaLeido
     *
     * @param \DateTime $fechaLeido
     * @return Mensajeria
     */
    public function setFechaLeido($fechaLeido)
    {
        $this->fechaLeido = $fechaLeido;

        return $this;
    }

    /**
     * Get fechaLeido
     *
     * @return \DateTime 
     */
    public function getFechaLeido()
    {
        return $this->fechaLeido;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Mensajeria
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
     * @return Mensajeria
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
     * Set destinatario
     *
     * @param \ConfigBundle\Entity\Usuario $destinatario
     * @return Mensajeria
     */
    public function setDestinatario(\ConfigBundle\Entity\Usuario $destinatario = null)
    {
        $this->destinatario = $destinatario;

        return $this;
    }

    /**
     * Get destinatario
     *
     * @return \ConfigBundle\Entity\Usuario 
     */
    public function getDestinatario()
    {
        return $this->destinatario;
    }

    /**
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return Mensajeria
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
