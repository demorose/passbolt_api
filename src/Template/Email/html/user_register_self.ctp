<?php
/**
 * Passbolt ~ Open source password manager for teams
 * Copyright (c) Passbolt SARL (https://www.passbolt.com)
 *
 * Licensed under GNU Affero General Public License version 3 of the or any later version.
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Passbolt SARL (https://www.passbolt.com)
 * @license       https://opensource.org/licenses/AGPL-3.0 AGPL License
 * @link          https://www.passbolt.com Passbolt(tm)
 * @since         2.0.0
 */
use Cake\Routing\Router;

$user = $body['user'];
$token = $body['token'];

echo $this->element('email/module/avatar',[
    // @TODO avatar url in email
    'url' => Router::url('/img/avatar' . DS . 'user.png', true),
    'text' => $this->element('email/module/avatar_text', [
        'username' => $user->username,
        'first_name' => $user->profile->first_name,
        'last_name' => $user->profile->last_name,
        'datetime' => $user->created,
        'text' => __('You have just created your account on passbolt!')
    ])
]);

$text = '<h3>' . __('Welcome {0}', $user->profile->first_name) . ',</h3><br/>';
$text .= __('You just opened an account on passbolt at {0}.',
    '<a href="' . Router::url('/',true) . '">' . Router::url('/',true) . '</a>'
);
$text .= ' ' . __('Passbolt is an open source password manager.');
$text .= ' ' .__('It is designed to allow sharing credentials securely with your team!');
$text .= '<br/><br/>';
$text .= __('Let\'s take the next five minutes to get you started!');
$text .= '<br/>';
echo $this->element('email/module/text', [
    'text' => $text
]);

echo $this->element('email/module/button', [
    'url' => Router::url('/setup/install/' . $user['id'] . '/' . $token['token']),
    'text' => __('get started')
]);