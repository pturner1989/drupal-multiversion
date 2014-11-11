<?php

/**
 * @file
 * Contains \Drupal\Tests\multiversion\Unit\SessionWorkspaceNegotiatorTest.
 */

namespace Drupal\Tests\multiversion\Unit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Url;
use Drupal\multiversion\Workspace\SessionWorkspaceNegotiator;
use Drupal\multiversion\Workspace\WorkspaceManager;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * @coversDefaultClass \Drupal\multiversion\Workspace\SessionWorkspaceNegotiator
 * @group multiversion
 */
class SessionWorkspaceNegotiatorTest extends UnitTestCase {

  /**
   * The workspace negotiator.
   *
   * @var \Drupal\multiversion\Workspace\SessionWorkspaceNegotiator
   */
  protected $workspaceNegotiator;

  /**
   * The request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityManager;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $requestStack;

  /**
   * @var \Drupal\multiversion\Workspace\WorkspaceManagerInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $workspaceManager;

  /**
   * The path used for testing.
   *
   * @var string
   */
  protected $path;

  /**
   * The entity type used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityType;

  /**
   * @var \Drupal\multiversion\Workspace\SessionWorkspaceNegotiator|PHPUnit_Framework_MockObject_MockObject
   */
  protected $negotiator;

  /**
   * The entities values.
   *
   * @var array
   */
  protected $values;

  /**
   * The id of the default entity.
   *
   * @var string
   */
  protected $defaultId = 'default';

  /**
   * The entity type used for testing.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface|\PHPUnit_Framework_MockObject_MockObject
   */
  protected $entityTypeId;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->entityTypeId = 'workspace';
    $second_id = $this->randomMachineName();
    $this->values = array(
      array(
        'id' => $this->defaultId,
        'label' => $this->defaultId,
        'created' => (int) microtime(TRUE) * 1000000,
      ),
      array(
        'id' => $second_id,
        'label' => $second_id,
        'created' => (int) microtime(TRUE) * 1000000,
      ),
    );

    $methods = get_class_methods('\Drupal\multiversion\Entity\Workspace');
    foreach ($this->values as $value) {
      $this->entities[] = $this->getMock('\Drupal\multiversion\Entity\Workspace', $methods, array($value, $this->entityTypeId));
    }

    $this->path = '<front>';
    $this->request = Request::create($this->path);

    $this->entityType = $this->getMock('\Drupal\multiversion\Entity\WorkspaceInterface');
    $this->entityManager = $this->getMock('\Drupal\Core\Entity\EntityManagerInterface');
    $this->entityManager->expects($this->any())
      ->method('getDefinition')
      ->with($this->entityTypeId)
      ->will($this->returnValue($this->entityType));
    $this->requestStack = $this->getMock('\Symfony\Component\HttpFoundation\RequestStack');

    $methods = get_class_methods('\Drupal\multiversion\Workspace\WorkspaceManagerInterface');
    $this->workspaceManager = $this->getMock(
      '\Drupal\multiversion\Workspace\WorkspaceManagerInterface',
      $methods,
      array($this->requestStack, $this->entityManager)
    );

    $container = new ContainerBuilder();
    $container->setParameter('workspace.default', $this->defaultId);
    $container->set('entity.manager', $this->entityManager);
    $container->set('workspace.manager', $this->workspaceManager);
    $container->set('request_stack', $this->requestStack);
    \Drupal::setContainer($container);

    $this->workspaceNegotiator = new SessionWorkspaceNegotiator();
    $this->workspaceNegotiator->setContainer($container);
  }

  /**
   * Tests the applies() method.
   *
   * @covers ::applies()
   */
  public function testApplies() {
    $this->assertTrue($this->workspaceNegotiator->applies($this->request));
  }

  /**
   * Tests the getWorkspaceId() method.
   *
   * @covers ::getWorkspaceId()
   */
  public function testGetWorkspaceId() {
    $this->assertSame($this->defaultId, $this->workspaceNegotiator->getWorkspaceId($this->request));
  }

  /**
   * Tests the persist() method.
   *
   * @covers ::persist()
   */
  public function testPersist() {
    $this->entities[0]->expects($this->any())
      ->method('id')
      ->will($this->returnValue($this->defaultId));
    $this->assertTrue($this->workspaceNegotiator->persist($this->entities[0]));
    $this->assertSame($this->entities[0]->id(), $_SESSION['workspace']);
  }

  /**
   * Tests the getWorkspaceSwitchLinks() method.
   *
   * @covers ::getWorkspaceSwitchLinks()
   */
  public function testGetWorkspaceSwitchLinks() {
    $query = array();
    parse_str($this->request->getQueryString(), $query);
    $second_id = $this->values[1]['id'];
    $url = Url::fromRoute($this->path);
    $expected_links = array(
      $this->defaultId => array(
        'url' => $url,
        'title' => $this->defaultId,
        'query' => $query,
        'attributes' => array(
          'class' => array('session-active'),
        ),
      ),
      $second_id => array(
        'url' => $url,
        'title' => $second_id,
        'query' => array(
          'workspace' => $second_id,
        ),
      ),
    );

    $this->workspaceManager->addNegotiator($this->workspaceNegotiator, 1);
    $this->workspaceManager->setActiveWorkspace($this->entities[0]);

    $methods = get_class_methods('\Drupal\multiversion\Workspace\SessionWorkspaceNegotiator');
    $this->negotiator = $this->getMock('\Drupal\multiversion\Workspace\SessionWorkspaceNegotiator', $methods);
    $this->negotiator->setWorkspaceManager($this->workspaceManager);
    $this->negotiator->expects($this->any())
      ->method('setWorkspaceManager')
      ->with($this->workspaceManager);

    foreach ($this->values as $key => $value) {
      $this->entities[$key]->expects($this->any())
        ->method('id')
        ->will($this->returnValue($value['id']));
    }

    $this->negotiator->expects($this->any())
      ->method('getActiveWorkspace')
      ->with($this->requestStack, $this->entityManager)
      ->will($this->returnValue($this->defaultId));

    $storage = $this->getMock('\Drupal\Core\Entity\EntityStorageInterface');
    $storage->expects($this->any())
      ->method('loadMultiple')
      ->with()
      ->will($this->returnValue($this->entities));

    $this->entityManager->expects($this->any())
      ->method('getStorage')
      ->with('workspace')
      ->will($this->returnValue($storage));

    $this->workspaceManager->expects($this->any())
      ->method('loadMultiple')
      ->with()
      ->will($this->returnValue(array($this->entities)));

    $workspace_manager = new WorkspaceManager($this->requestStack, $this->entityManager);
    $workspace_manager->addNegotiator($this->workspaceNegotiator, 1);
    $workspace_manager->setActiveWorkspace($this->entities[0]);
    $negotiator = new SessionWorkspaceNegotiator();
    $negotiator->setWorkspaceManager($workspace_manager);

    $links = $negotiator->getWorkspaceSwitchLinks($this->request, $url);
    $this->assertSame($expected_links, $links);
  }
}
