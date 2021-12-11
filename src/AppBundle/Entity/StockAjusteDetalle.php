<?php
namespace AppBundle\Entity;
use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
/**
 * AppBundle\Entity\StockAjsuteDetalle
 *
 * @ORM\Table(name="stock_ajuste_detalle")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\StockRepository")
 */
class StockAjusteDetalle
{
    /**
     * @var integer $id
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;
    
    /**
     * @var string $signo
     * @ORM\Column(name="signo", type="string")
     */
    private $signo='+';
    
     /**
     * @var integer $cantidad
     * @ORM\Column(name="cantidad", type="decimal", scale=2)
     */
    protected $cantidad;
 
     /**
     * @ORM\Column(name="motivo", type="text", nullable=true)
     */
    protected $motivo; 
    
    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Insumo")
     * @ORM\JoinColumn(name="insumo_id", referencedColumnName="id",onDelete="CASCADE")
     */
    protected $insumo; 

     /**
     *@ORM\ManyToOne(targetEntity="AppBundle\Entity\StockAjuste", inversedBy="detalles")
     *@ORM\JoinColumn(name="stock_ajuste_id", referencedColumnName="id") 
     */
    protected $stockAjuste;    


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
     * Set signo
     *
     * @param string $signo
     * @return StockAjusteDetalle
     */
    public function setSigno($signo)
    {
        $this->signo = $signo;

        return $this;
    }

    /**
     * Get signo
     *
     * @return string 
     */
    public function getSigno()
    {
        return $this->signo;
    }

    /**
     * Set cantidad
     *
     * @param string $cantidad
     * @return StockAjusteDetalle
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
     * Set motivo
     *
     * @param string $motivo
     * @return StockAjusteDetalle
     */
    public function setMotivo($motivo)
    {
        $this->motivo = $motivo;

        return $this;
    }

    /**
     * Get motivo
     *
     * @return string 
     */
    public function getMotivo()
    {
        return $this->motivo;
    }

    /**
     * Set insumo
     *
     * @param \AppBundle\Entity\Insumo $insumo
     * @return StockAjusteDetalle
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
     * Set stockAjuste
     *
     * @param \AppBundle\Entity\StockAjuste $stockAjuste
     * @return StockAjusteDetalle
     */
    public function setStockAjuste(\AppBundle\Entity\StockAjuste $stockAjuste = null)
    {
        $this->stockAjuste = $stockAjuste;

        return $this;
    }

    /**
     * Get stockAjuste
     *
     * @return \AppBundle\Entity\StockAjuste 
     */
    public function getStockAjuste()
    {
        return $this->stockAjuste;
    }
}
