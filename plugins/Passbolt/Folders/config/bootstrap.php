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

use App\Command\CleanupCommand;
use Cake\Core\Configure;
use Cake\Event\EventManager;
use Passbolt\Folders\EventListener\AddFolderizableBehavior;
use Passbolt\Folders\EventListener\GroupsEventListener;
use Passbolt\Folders\EventListener\PermissionsModelInitializeEventListener;
use Passbolt\Folders\EventListener\ResourcesEventListener;
use Passbolt\Folders\Notification\Email\FoldersEmailRedactorPool;
use Passbolt\Folders\Notification\NotificationSettings\FolderNotificationSettingsDefinition;

Configure::load('Passbolt/Folders.config', 'default', true);

EventManager::instance()
    ->on(new ResourcesEventListener()) //Add / remove folders relations when a resources is created / deleted
    ->on(new GroupsEventListener()) // Add / remove folders relations when a group members list is updated
    ->on(new AddFolderizableBehavior()) // Decorate the core/other plugins table classes that can be organized in folder
    ->on(new PermissionsModelInitializeEventListener()) // Decorate the permissions table class to add cleanup method
    ->on(new FolderNotificationSettingsDefinition())// Add email notification settings definition
    ->on(new FoldersEmailRedactorPool()); // Register email redactors

// Add cleanup tasks jobs.
if (PHP_SAPI === 'cli') {
    $cleanups = [
        'Permissions' => [
            'Hard Deleted Folders',
        ],
        'Passbolt/Folders.FoldersRelations' => [
            'Hard Deleted Users',
            'Soft Deleted Users',
            'Hard Deleted Resources',
            'Soft Deleted Resources',
            'Hard Deleted Folders',
            'Hard Deleted Folders Parents',
            'Missing Folders Folders Relations', // Ensure this cleanup is run before 'Missing Resources Folders Relations'
            'Missing Resources Folders Relations',
            'Duplicated Folders Relations',
        ],
    ];
    CleanupCommand::addCleanups($cleanups);
}