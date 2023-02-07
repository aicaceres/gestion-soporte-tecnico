<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * AppBundle\Entity\Insumo
 * @ORM\Table(name="insumo",uniqueConstraints={@ORM\UniqueConstraint(name="insumo_idx", columns={"tipo_id","marca_id","modelo_id"})})
 * @ORM\Entity(repositoryClass="AppBundle\Entity\InsumoRepository")
 * @UniqueEntity(
 *     fields={"tipo","marca","modelo"}, errorPath="tipo",
 *     message="Ya existe un insumo de este tipo, marca y modelo."
 * )
 * @ORM\HasLifecycleCallbacks
 * @Gedmo\SoftDeleteable(fieldName="deletedAt", timeAware=false)
 * @Gedmo\Loggable()
 */
class Insumo {
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string $nombre
     * @ORM\Column(name="nombre", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $nombre;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Tipo",inversedBy="insumos")
     * @ORM\JoinColumn(name="tipo_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $tipo;

    /**
     * @var string $codigo
     * @ORM\Column(name="codigo", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $codigo;

    /**
     * @var string $barcode
     * @ORM\Column(name="barcode", type="string", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $barcode;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Marca")
     * @ORM\JoinColumn(name="marca_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $marca;

    /**
     * @ORM\ManyToOne(targetEntity="ConfigBundle\Entity\Modelo")
     * @ORM\JoinColumn(name="modelo_id", referencedColumnName="id")
     * @Gedmo\Versioned()
     */
    protected $modelo;

    /**
     * @var string $stockMinimo
     * @ORM\Column(name="stock_minimo", type="integer", nullable=true)
     * @Gedmo\Versioned()
     */
    protected $stockMinimo = 0;

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
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Stock", mappedBy="insumo")
     */
    protected $stock;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\StockHistorico", mappedBy="insumo")
     */
    protected $stockHistorico;

    public function __toString() {
        return $this->getTextoCompleto();
    }

    public function getTextoCompleto() {
        $marca = ( $this->getMarca() ) ? $this->getMarca()->getNombre() : '';
        $modelo = ( $this->getModelo() ) ? $this->getModelo()->getNombre() : '';
        return $this->getTipo()->getNombre() . ' │ ' . $marca . ' │ ' . $modelo;
    }

    public function getTexto() {
        $marca = ( $this->getMarca() ) ? $this->getMarca()->getNombre() : '';
        $modelo = ( $this->getModelo() ) ? $this->getModelo()->getNombre() : '';
        return $this->getBarcode() . ' - ' . $this->getTipo()->getNombre() . ' - ' . $marca . ' - ' . $modelo;
    }

    public function getCodigoItem() {
        return ( $this->barcode ) ? $this->barcode : $this->codigo;
    }

    public function getStockByDeposito($dep) {
        foreach ($this->getStock() as $stock) {
            if ($stock->getDeposito()->getId() == $dep) {
                return ($stock->getCantidad()) ? $stock->getCantidad() : 0;
            }
        }
        return 0;
    }

    public function getStockTotal() {
        $total = 0;
        foreach ($this->getStock() as $stock) {
            $total = $total + ( $stock->getCantidad() ? $stock->getCantidad() : 0);
        }
        return $total;
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
     * Set nombre
     *
     * @param string $nombre
     * @return Insumo
     */
    public function setNombre($nombre) {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * Get nombre
     *
     * @return string
     */
    public function getNombre() {
        return $this->nombre;
    }

    /**
     * Set barcode
     *
     * @param string $barcode
     * @return Insumo
     */
    public function setBarcode($barcode) {
        $this->barcode = $barcode;

        return $this;
    }

    /**
     * Get barcode
     *
     * @return string
     */
    public function getBarcode() {
        return $this->barcode;
    }

    /**
     * Set stockMinimo
     *
     * @param integer $stockMinimo
     * @return Insumo
     */
    public function setStockMinimo($stockMinimo) {
        $this->stockMinimo = $stockMinimo;

        return $this;
    }

    /**
     * Get stockMinimo
     *
     * @return integer
     */
    public function getStockMinimo() {
        return $this->stockMinimo;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Insumo
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
     * @return Insumo
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
     * @return Insumo
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
     * Set tipo
     *
     * @param \ConfigBundle\Entity\Tipo $tipo
     * @return Insumo
     */
    public function setTipo(\ConfigBundle\Entity\Tipo $tipo = null) {
        $this->tipo = $tipo;

        return $this;
    }

    /**
     * Get tipo
     *
     * @return \ConfigBundle\Entity\Tipo
     */
    public function getTipo() {
        return $this->tipo;
    }

    /**
     * Set marca
     *
     * @param \ConfigBundle\Entity\Marca $marca
     * @return Insumo
     */
    public function setMarca(\ConfigBundle\Entity\Marca $marca = null) {
        $this->marca = $marca;

        return $this;
    }

    /**
     * Get marca
     *
     * @return \ConfigBundle\Entity\Marca
     */
    public function getMarca() {
        return $this->marca;
    }

    /**
     * Set modelo
     *
     * @param \ConfigBundle\Entity\Modelo $modelo
     * @return Insumo
     */
    public function setModelo(\ConfigBundle\Entity\Modelo $modelo = null) {
        $this->modelo = $modelo;

        return $this;
    }

    /**
     * Get modelo
     *
     * @return \ConfigBundle\Entity\Modelo
     */
    public function getModelo() {
        return $this->modelo;
    }

    /**
     * Set createdBy
     *
     * @param \ConfigBundle\Entity\Usuario $createdBy
     * @return Insumo
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
     * @return Insumo
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
     * Set codigo
     *
     * @param string $codigo
     * @return Insumo
     */
    public function setCodigo($codigo) {
        $this->codigo = $codigo;

        return $this;
    }

    /**
     * Get codigo
     *
     * @return string
     */
    public function getCodigo() {
        return $this->codigo;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->stock = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add stock
     *
     * @param \AppBundle\Entity\Stock $stock
     * @return Insumo
     */
    public function addStock(\AppBundle\Entity\Stock $stock) {
        $this->stock[] = $stock;

        return $this;
    }

    /**
     * Remove stock
     *
     * @param \AppBundle\Entity\Stock $stock
     */
    public function removeStock(\AppBundle\Entity\Stock $stock) {
        $this->stock->removeElement($stock);
    }

    /**
     * Get stock
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStock() {
        return $this->stock;
    }

    /**
     * Add stockHistorico
     *
     * @param \AppBundle\Entity\StockHistorico $stockHistorico
     * @return Insumo
     */
    public function addStockHistorico(\AppBundle\Entity\StockHistorico $stockHistorico) {
        $this->stockHistorico[] = $stockHistorico;

        return $this;
    }

    /**
     * Remove stockHistorico
     *
     * @param \AppBundle\Entity\StockHistorico $stockHistorico
     */
    public function removeStockHistorico(\AppBundle\Entity\StockHistorico $stockHistorico) {
        $this->stockHistorico->removeElement($stockHistorico);
    }

    /**
     * Get stockHistorico
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStockHistorico() {
        return $this->stockHistorico;
    }

}