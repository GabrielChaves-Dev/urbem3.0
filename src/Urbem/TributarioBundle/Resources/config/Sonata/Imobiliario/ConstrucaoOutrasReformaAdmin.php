<?php

namespace Urbem\TributarioBundle\Resources\config\Sonata\Imobiliario;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\HttpFoundation\Request;
use Urbem\CoreBundle\Entity\Imobiliario\AreaConstrucao;
use Urbem\CoreBundle\Entity\Imobiliario\AreaUnidadeDependente;
use Urbem\CoreBundle\Entity\Imobiliario\Construcao;
use Urbem\CoreBundle\Entity\Imobiliario\ConstrucaoProcesso;
use Urbem\CoreBundle\Entity\SwAssunto;
use Urbem\CoreBundle\Entity\SwClassificacao;
use Urbem\CoreBundle\Entity\SwProcesso;
use Urbem\CoreBundle\Model\Imobiliario\ConstrucaoModel;
use Urbem\CoreBundle\Resources\config\Sonata\AbstractSonataAdmin as AbstractAdmin;
use Sonata\AdminBundle\Form\FormMapper;

class ConstrucaoOutrasReformaAdmin extends AbstractAdmin
{

    protected $baseRouteName = 'urbem_tributario_imobiliario_construcao_reforma';
    protected $baseRoutePattern = 'tributario/cadastro-imobiliario/construcao-reforma';
    protected $legendButtonSave = array('icon' => 'build', 'text' => 'Incluir');
    protected $includeJs = array(
        '/tributario/javascripts/imobiliario/processo.js'
    );

    /**
     * @param Construcao $construcao
     * @return boolean
     */
    public function verificaBaixa(Construcao $construcao)
    {
        $em = $this->modelManager->getEntityManager($this->getClass());
        return (new ConstrucaoModel($em))->verificaBaixa($construcao);
    }

    /**
     * @param RouteCollection $collection
     */
    public function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('create'));
    }

    /**
     * @return array
     */
    public function getPersistentParameters()
    {
        if (!$this->getRequest()) {
            return array();
        }

        return array(
            'codConstrucao' => $this->getRequest()->get('codConstrucao'),
        );
    }

    /**
     * @return null|Construcao
     */
    public function getConstrucao()
    {
        /** @var EntityManager $em */
        $em = $this->modelManager->getEntityManager($this->getClass());

        $construcao = null;
        if ($this->getPersistentParameter('codConstrucao')) {
            /** @var Construcao $construcao */
            $construcao = $em->getRepository(Construcao::class)->find($this->getPersistentParameter('codConstrucao'));
        }
        return $construcao;
    }

    /**
     * @param FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $id = $this->getAdminRequestId();

        $this->setBreadCrumb($id ? ['id' => $id] : []);

        $construcao = $this->getConstrucao();

        $fieldOptions = array();

        $fieldOptions['dadosConstrucao'] = array(
            'label' => false,
            'mapped' => false,
            'template' => 'TributarioBundle::Imobiliario/Construcao/dadosConstrucao.html.twig',
            'data' => array(
                'construcao' => $construcao
            )
        );

        $fieldOptions['area'] = array(
            'label' => 'label.imobiliarioConstrucao.area',
            'mapped' => false,
            'required' => true,
            'attr' => [
                'class' => 'money '
            ]
        );

        $fieldOptions['fkSwClassificacao'] = array(
            'label' => 'label.imobiliarioConstrucao.classificacao',
            'class' => SwClassificacao::class,
            'placeholder' => 'label.selecione',
            'mapped' => false,
            'required' => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('o')
                    ->orderBy('o.codClassificacao', 'ASC');
            },
            'attr' => array(
                'class' => 'select2-parameters '
            )
        );

        $fieldOptions['fkSwAssunto'] = array(
            'label' => 'label.imobiliarioConstrucao.assunto',
            'class' => SwAssunto::class,
            'choice_value' => 'codAssunto',
            'placeholder' => 'label.selecione',
            'mapped' => false,
            'required' => false,
            'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('o')
                    ->orderBy('o.codAssunto', 'ASC');
            },
            'attr' => array(
                'class' => 'select2-parameters '
            )
        );

        $fieldOptions['fkSwProcesso'] = array(
            'label' => 'label.imobiliarioConstrucao.processo',
            'class' => SwProcesso::class,
            'req_params' => [
                'codClassificacao' => 'varJsCodClassificacao',
                'codAssunto' => 'varJsCodAssunto'
            ],
            'json_from_admin_code' => $this->code,
            'json_query_builder' => function (EntityRepository $er, $term, Request $request) {
                $qb = $er->createQueryBuilder('o');
                $qb->innerJoin('o.fkAdministracaoUsuario', 'u');
                $qb->innerJoin('u.fkSwCgm', 'cgm');
                if ($request->get('codClassificacao') != '') {
                    $qb->andWhere('o.codClassificacao = :codClassificacao');
                    $qb->setParameter('codClassificacao', (int) $request->get('codClassificacao'));
                }
                if ($request->get('codAssunto') != '') {
                    $qb->andWhere('o.codAssunto = :codAssunto');
                    $qb->setParameter('codAssunto', (int) $request->get('codAssunto'));
                }
                $qb->andWhere($qb->expr()->orX(
                    $qb->expr()->eq('o.codProcesso', ':codProcesso'),
                    $qb->expr()->eq('cgm.numcgm', ':numCgm'),
                    $qb->expr()->like('LOWER(cgm.nomCgm)', $qb->expr()->literal('%' . strtolower($term) . '%'))
                ));
                $qb->setParameter('numCgm', (int) $term);
                $qb->setParameter('codProcesso', (int) $term);
                $qb->orderBy('o.codProcesso', 'ASC');
                return $qb;
            },
            'mapped' => false,
            'required' => false,
        );

        $formMapper->with('label.imobiliarioConstrucao.dadosReforma');
        $formMapper->add('dadosConstrucao', 'customField', $fieldOptions['dadosConstrucao']);
        $formMapper->add('area', 'text', $fieldOptions['area']);
        $formMapper->end();
        $formMapper->with('label.imobiliarioConstrucao.processo');
        $formMapper->add('fkSwClassificacao', 'entity', $fieldOptions['fkSwClassificacao']);
        $formMapper->add('fkSwAssunto', 'entity', $fieldOptions['fkSwAssunto']);
        $formMapper->add('fkSwProcesso', 'autocomplete', $fieldOptions['fkSwProcesso']);
        $formMapper->end();
    }

    /**
     * @param Construcao $construcao
     */
    public function prePersist($construcao)
    {
        $container = $this->getConfigurationPool()->getContainer();

        try {
            /** @var EntityManager $em */
            $em = $this->modelManager->getEntityManager($this->getClass());

            /** @var Construcao $construcao */
            $construcao = $this->getConstrucao();

            $url = '/tributario/cadastro-imobiliario/construcao/imovel/list';

            if ($this->getForm()->get('fkSwProcesso')->getData()) {
                $construcaoProcesso = new ConstrucaoProcesso();
                $construcaoProcesso->setFkSwProcesso($this->getForm()->get('fkSwProcesso')->getData());
                $construcao->addFkImobiliarioConstrucaoProcessos($construcaoProcesso);
            }

            if ($construcao->getFkImobiliarioUnidadeDependentes()->count()) {
                $areaUnidadeDependente = new AreaUnidadeDependente();
                $areaUnidadeDependente->setArea($this->getForm()->get('area')->getData());
                $construcao->getFkImobiliarioUnidadeDependentes()->last()->addFkImobiliarioAreaUnidadeDependentes($areaUnidadeDependente);
            } else {
                $areaConstrucao = new AreaConstrucao();
                $areaConstrucao->setAreaReal($this->getForm()->get('area')->getData());
                $construcao->addFkImobiliarioAreaConstrucoes($areaConstrucao);
            }

            if ($construcao->getFkImobiliarioConstrucaoCondominios()->count()) {
                $url = '/tributario/cadastro-imobiliario/construcao/condominio/list';
            }

            $em->persist($construcao);
            $em->flush();

            $container->get('session')->getFlashBag()->add('success', $this->getTranslator()->trans('label.imobiliarioConstrucao.msgReforma', array('%codConstrucao%' => $construcao->getCodConstrucao())));
            $this->forceRedirect($url);
        } catch (\Exception $e) {
            $container->get('session')->getFlashBag()->add('error', $this->getTranslator()->trans('contactSupport'));
            $container->get('session')->getFlashBag()->add('error', $e->getMessage());
            $this->forceRedirect($url);
        }
    }
}
