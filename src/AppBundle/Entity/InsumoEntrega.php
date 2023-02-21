<?php

namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * AppBundle\Entity\InsumoEntrega
 * @ORM\Table(name="insumo_entrega")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\InsumoRepository")
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\Loggable()
 */
class InsumoEntrega {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var datetime $fecha
     * @ORM\Column(name="fecha", type="datetime", nullable=false)
     * @Gedmo\Versioned()
     */
    private $fecha;

    /**
     * @var string $jira
     * @ORM\Column(name="jira", type="string",length=20, nullable=true)
     * @Gedmo\Versioned()
     */
    protected $jira;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento")
     * @ORM\JoinColumn(name="solicitante_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $solicitante;

    /**
     * @var string $responsable
     * @ORM\Column(name="responsable", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $responsable;

    /**
     * @var text $observacion
     * @ORM\Column(name="observacion", type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $observacion;

    /**
     * @var text $textoRemito
     * @ORM\Column(name="texto_remito", type="text", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $textoRemito;

    /**
     * @var string $estado
     * @ORM\Column(name="estado", type="string")
     * @Gedmo\Versioned()
     */
    /* PENDIENTE - ENTREGADO - CANCELADO */
    protected $estado = 'ENTREGADO';

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Departamento")
     * @ORM\JoinColumn(name="deposito_id", referencedColumnName="id")
     */
    protected $deposito;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\InsumoEntregaDetalle", mappedBy="entrega", orphanRemoval=true,cascade={"persist", "remove"})
     */
    protected $detalles;

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
     * @ORM\Column(name="deletedAt", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * Constructor
     */
    public function __construct() {
        $this->detalles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function esPedidoInsumoSoporte() {
        foreach ($this->detalles as $detalle) {
            if ($detalle->getInsumoxTarea()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set fecha
     *
     * @param \DateTime $fecha
     * @return InsumoEntrega
     */
    public function setFecha($fecha) {
        $this->fecha = $fecha;

        return $this;
    }

    /**
     * Get fecha
     *
     * @return \DateTime
     */
    public function getFecha() {
        return $this->fecha;
    }

    /**
     * Set jira
     *
     * @param string $jira
     * @return InsumoEntrega
     */
    public function setJira($jira) {
        $this->jira = $jira;

        return $this;
    }

    /**
     * Get jira
     *
     * @return string
     */
    public function getJira() {
        return $this->jira;
    }

    /**
     * Set responsable
     *
     * @param string $responsable
     * @return InsumoEntrega
     */
    public function setResponsable($responsable) {
        $this->responsable = $responsable;

        return $this;
    }

    /**
     * Get responsable
     *
     * @return string
     */
    public function getResponsable() {
        return $this->responsable;
    }

    /**
     * Set observacion
     *
     * @param string $observacion
     * @return InsumoEntrega
     */
    public function setObservacion($observacion) {
        $this->observacion = $observacion;

        return $this;
    }

    /**
     * Get observacion
     *
     * @return string
     */
    public function getObservacion() {
        return $this->observacion;
    }

    /**
     * Set textoRemito
     *
     * @param string $textoRemito
     * @return InsumoEntrega
     */
    public function setTextoRemito($textoRemito) {
        $this->textoRemito = $textoRemito;

        return $this;
    }

    /**
     * Get textoRemito
     *
     * @return string
     */
    public function getTextoRemito() {
        return $this->textoRemito;
    }

    /**
     * Set estado
     *
     * @param string $estado
     * @return InsumoEntrega
     */
    public function setEstado($estado) {
        $this->estado = $estado;

        return $this;
    }

    /**
     * Get estado
     *
     * @return string
     */
    public function getEstado() {
        return $this->estado;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return InsumoEntrega
     */
    public function setCreated($created) {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return InsumoEntrega
     */
    public function setUpdated($updated) {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated() {
        return $this->updated;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     * @return InsumoEntrega
     */
    public function setDeletedAt($deletedAt) {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt() {
        return $this->deletedAt;
    }

    /**
     * Set solicitante
     *
     * @param \ConfigBundle\Entity\Departamento $solicitante
     * @return InsumoEntrega
     */
    public function setSolicitante(\ConfigBundle\Entity\Departamento $solicitante = null) {
        $this->solicitante = $solicitante;

        return $this;
    }

    /**
     * Get solicitante
     *
     * @return \ConfigBundle\Entity\Departamento
     */
    public function getSolicitante() {
        return $this->solicitante;
    }

    /**
     * Add detalles
     *
     * @param \AppBundle\Entity\InsumoEntregaDetalle $detalles
     * @return InsumoEntrega
     */
    public function addDetalle(\AppBundle\Entity\InsumoEntregaDetalle $detalles) {
        $detalles->setEntrega($this);
        $this->detalles[] = $detalles;

        return $this;
    }

    /**
     * Remove detalles
     *
     * @param \AppBundle\Entity\InsumoEntregaDetalle $detalles
     */
    public function removeDetalle(\AppBundle\Entity\InsumoEntregaDetalle $detalles) {
        $this->detalles->removeElement($detalles);
    }

    /**
     * Get detalles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDetalles() {
        return $this->detalles;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return InsumoEntrega
     */
    public function setCreatedBy(\ConfigBundle\Entity\Usuario $createdBy = null) {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getCreatedBy() {
        return $this->createdBy;
    }

    /**
     * Set updatedBy
     *
     * @param \ConfigBundle\Entity\Usuario $updatedBy
     * @return InsumoEntrega
     */
    public function setUpdatedBy(\ConfigBundle\Entity\Usuario $updatedBy = null) {
        $this->updatedBy = $updatedBy;

        return $this;
    }

    /**
     * Get updatedBy
     *
     * @return \ConfigBundle\Entity\Usuario
     */
    public function getUpdatedBy() {
        return $this->updatedBy;
    }

    /**
     * Set deposito
     *
     * @param \ConfigBundle\Entity\Departamento $deposito
     * @return InsumoEntrega
     */
    public function setDeposito(\ConfigBundle\Entity\Departamento $deposito = null) {
        $this->deposito = $deposito;

        return $this;
    }

    /**
     * Get deposito
     *
     * @return \ConfigBundle\Entity\Departamento
     */
    public function getDeposito() {
        return $this->deposito;
    }

}