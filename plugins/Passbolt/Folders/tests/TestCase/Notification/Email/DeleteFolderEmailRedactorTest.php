<?php
/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         2.13.0
 */

namespace Passbolt\Folders\Test\TestCase\Notification\Email;

use App\Model\Entity\Profile;
use App\Model\Entity\User;
use App\Model\Table\UsersTable;
use App\Utility\UserAccessControl;
use App\Utility\UuidFactory;
use Cake\Event\Event;
use Passbolt\Folders\Model\Entity\Folder;
use Passbolt\Folders\Notification\Email\DeleteFolderEmailRedactor;
use Passbolt\Folders\Service\Folders\FoldersDeleteService;
use Passbolt\Folders\Test\Lib\FoldersTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SA (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SA (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         2.13.0
 */
class DeleteFolderEmailRedactorTest extends FoldersTestCase
{
    /**
     * @var DeleteFolderEmailRedactor
     */
    private $sut;

    /**
     * @var MockObject|UsersTable
     */
    private $usersTableMock;

    public function setUp()
    {
        $this->usersTableMock = $this->createMock(UsersTable::class);
        $this->sut = new DeleteFolderEmailRedactor($this->usersTableMock);

        parent::setUp();
    }

    /**
     * @return void
     */
    public function testThatEmailSubscriberIsSubscribedToCorrectEvent()
    {
        $this->assertContains(FoldersDeleteService::FOLDERS_DELETE_FOLDER_EVENT, $this->sut->getSubscribedEvents());
    }

    /**
     * @return void
     */
    public function testThatEmailProvideUserAndFolderData()
    {
        $user = new User();
        $folder = new Folder();

        $user->username = 'admin@passbolt.com';
        $user->profile = new Profile();
        $user->profile->first_name = 'Ada';
        $folder->id = UuidFactory::uuid();
        $folder->name = 'FolderName';

        $event = (new Event(FoldersDeleteService::FOLDERS_DELETE_FOLDER_EVENT))->setData([
            'folder' => $folder,
            'uac' => new UserAccessControl('', UuidFactory::uuid()),
        ]);

        $this->usersTableMock->expects($this->once())
            ->method('findFirstForEmail')
            ->willReturn($user);

        // Get email collections from subscriber
        $emailCollection = $this->sut->onSubscribedEvent($event);

        // Only 1 email should be present
        $this->assertCount(1, $emailCollection->getEmails());

        // Retrieve the first email from the collection
        $email = $emailCollection->getEmails()[0];

        // Assert email data are correct
        $expectedSubject = __("{0} deleted the folder {1}", $user->profile->first_name, $folder->name);
        $this->assertEquals($expectedSubject, $email->getSubject());
        $this->assertEquals('Passbolt/Folders.LU/folder_delete', $email->getTemplate());
        $this->assertEquals(
            [
                'title' => $expectedSubject,
                'body' => [
                    'user' => $user,
                    'folder' => $folder,
                ],
            ],
            $email->getData()
        );
    }
}