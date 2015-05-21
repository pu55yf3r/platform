<?php

namespace Oro\Bundle\LDAPBundle\ImportExport;

use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Item\InvalidItemException;

use Oro\Bundle\ImportExportBundle\Context\ContextInterface;
use Oro\Bundle\ImportExportBundle\Context\ContextRegistry;
use Oro\Bundle\ImportExportBundle\Processor\StepExecutionAwareProcessor;
use Oro\Bundle\IntegrationBundle\Entity\Channel;
use Oro\Bundle\IntegrationBundle\Provider\ConnectorContextMediator;
use Oro\Bundle\LDAPBundle\LDAP\Factory\LdapManagerFactory;
use Oro\Bundle\LDAPBundle\LDAP\Ldap;
use Oro\Bundle\LDAPBundle\LDAP\LdapManager;
use Oro\Bundle\UserBundle\Entity\User;
use Oro\Bundle\UserBundle\Entity\UserManager;

class LdapUserImportProcessor implements StepExecutionAwareProcessor
{
    /** @var StepExecution */
    private $stepExecution;

    /** @var ContextRegistry */
    private $contextRegistry;

    /** @var UserManager */
    private $userManager;

    /** @var ConnectorContextMediator */
    private $connectorContextMediator;

    /** @var ContextInterface */
    private $context;

    /** @var Channel */
    private $channel;

    /** @var LdapManagerFactory */
    private $ldapManagerFactory;

    public function __construct(
        LdapManagerFactory $ldapManagerFactory,
        UserManager $userManager,
        ContextRegistry $contextRegistry,
        ConnectorContextMediator $connectorContextMediator
    ) {
        $this->contextRegistry = $contextRegistry;
        $this->userManager = $userManager;
        $this->connectorContextMediator = $connectorContextMediator;
        $this->ldapManagerFactory = $ldapManagerFactory;
    }

    /**
     * Creates new User from provided array of parameters.
     *
     * @param mixed $item User as array with parameters. Comes from LdapUserReader.
     *
     * @return \Oro\Bundle\UserBundle\Entity\User
     */
    private function createUser($item)
    {
        /** @var User $user */
        $user = $this->userManager->createUser();

        $this->getLdapManager()->hydrate($user, $item);

        // Set organization of user to same as on channel.
        $user->setOrganization($this->getChannel()->getOrganization());

        if (!$user->getPassword()) {
            $user->setPassword('');
        }

        $this->getContext()->incrementAddCount();

        return $user;
    }

    /**
     * @return LdapManager
     */
    private function getLdapManager()
    {
        return $this->ldapManagerFactory->getInstanceForChannel(
            $this->getChannel()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        try {
            $user = $this->userManager->findUserByUsername(
                $item[$this->getLdapManager()->getUsernameAttr()]
            );

            if ($user !== null) {
                $this->getLdapManager()->hydrate($user, $item);
                $this->getContext()->incrementUpdateCount();
            } else {
                $user = $this->createUser($item);
            }

            return $user;
        } catch (\Exception $ex) {
            $this->getContext()->addError($ex->getMessage());
            throw $ex;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution)
    {
        $this->stepExecution = $stepExecution;

        $this->context = $this->contextRegistry->getByStepExecution($this->stepExecution);
    }

    /**
     * Returns context of current batch.
     *
     * @return ContextInterface
     */
    protected function getContext()
    {
        return $this->context;
    }

    /**
     * Returns integration channel.
     *
     * @return Channel
     */
    protected function getChannel()
    {
        if ($this->channel === null || $this->context->getOption('channel') !== $this->channel->getId()) {
            $this->channel = $this->connectorContextMediator->getChannel($this->context);
        }

        return $this->channel;
    }
}