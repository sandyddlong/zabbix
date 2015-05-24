<?php
/*
** Zabbix
** Copyright (C) 2001-2015 Zabbix SIA
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
**/


require_once dirname(__FILE__).'/include/config.inc.php';
require_once dirname(__FILE__).'/include/hosts.inc.php';
require_once dirname(__FILE__).'/include/triggers.inc.php';
require_once dirname(__FILE__).'/include/forms.inc.php';

$page['title'] = _('Configuration of trigger prototypes');
$page['file'] = 'trigger_prototypes.php';

require_once dirname(__FILE__).'/include/page_header.php';

//	VAR		TYPE	OPTIONAL FLAGS	VALIDATION	EXCEPTION
$fields = [
	'parent_discoveryid' => [T_ZBX_INT, O_MAND, P_SYS,	DB_ID,		null],
	'triggerid' =>			[T_ZBX_INT, O_OPT, P_SYS,	DB_ID,		'(isset({form}) && ({form} == "update"))'],
	'type' =>				[T_ZBX_INT, O_OPT, null,	IN('0,1'),	null],
	'description' =>		[T_ZBX_STR, O_OPT, null,	NOT_EMPTY,	'isset({add}) || isset({update})', _('Name')],
	'expression' =>			[T_ZBX_STR, O_OPT, null,	NOT_EMPTY,	'isset({add}) || isset({update})', _('Expression')],
	'priority' =>			[T_ZBX_INT, O_OPT, null,	IN('0,1,2,3,4,5'), 'isset({add}) || isset({update})'],
	'comments' =>			[T_ZBX_STR, O_OPT, null,	null,		'isset({add}) || isset({update})'],
	'url' =>				[T_ZBX_STR, O_OPT, null,	null,		'isset({add}) || isset({update})'],
	'status' =>				[T_ZBX_STR, O_OPT, null,	null,		null],
	'input_method' =>		[T_ZBX_INT, O_OPT, null,	NOT_EMPTY,	'isset({toggle_input_method})'],
	'expr_temp' =>			[T_ZBX_STR, O_OPT, null,	NOT_EMPTY,	'(isset({add_expression}) || isset({and_expression}) || isset({or_expression}) || isset({replace_expression}))'],
	'expr_target_single' =>	[T_ZBX_STR, O_OPT, null,	NOT_EMPTY,	'(isset({and_expression}) || isset({or_expression}) || isset({replace_expression}))'],
	'dependencies' =>		[T_ZBX_INT, O_OPT, null,	DB_ID,		null],
	'new_dependence' =>		[T_ZBX_INT, O_OPT, null,	DB_ID.'{}>0', 'isset({add_dependence})'],
	'rem_dependence' =>		[T_ZBX_INT, O_OPT, null,	DB_ID,		null],
	'g_triggerid' =>		[T_ZBX_INT, O_OPT, null,	DB_ID,		null],
	'showdisabled' =>		[T_ZBX_INT, O_OPT, P_SYS,	IN('0,1'),	null],
	// actions
	'action' =>				[T_ZBX_STR, O_OPT, P_SYS|P_ACT,
								IN('"triggerprototype.massdelete","triggerprototype.massdisable",'.
									'"triggerprototype.massenable","triggerprototype.massupdate",'.
									'"triggerprototype.massupdateform"'
								),
								null
							],
	'visible' =>			[T_ZBX_STR, O_OPT, null,	null,		null],
	'toggle_input_method' =>[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'add_expression' => 	[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'and_expression' =>		[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'or_expression' =>		[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'replace_expression' =>	[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'remove_expression' =>	[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'test_expression' =>	[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'add_dependence' =>		[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'del_dependence' =>		[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'group_enable' =>		[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'group_disable' =>		[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'group_delete' =>		[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'copy' =>				[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'clone' =>				[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'add' =>				[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'update' =>				[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'massupdate' =>			[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'delete' =>				[T_ZBX_STR, O_OPT, P_SYS|P_ACT, null,	null],
	'cancel' =>				[T_ZBX_STR, O_OPT, P_SYS,	null,		null],
	'form' =>				[T_ZBX_STR, O_OPT, P_SYS,	null,		null],
	'form_refresh' =>		[T_ZBX_INT, O_OPT, null,	null,		null],
	// sort and sortorder
	'sort' =>				[T_ZBX_STR, O_OPT, P_SYS, IN('"description","priority","status"'),		null],
	'sortorder' =>			[T_ZBX_STR, O_OPT, P_SYS, IN('"'.ZBX_SORT_DOWN.'","'.ZBX_SORT_UP.'"'),	null]
];
$_REQUEST['showdisabled'] = getRequest('showdisabled', CProfile::get('web.triggers.showdisabled', 1));

check_fields($fields);

$_REQUEST['status'] = isset($_REQUEST['status']) ? TRIGGER_STATUS_ENABLED : TRIGGER_STATUS_DISABLED;
$_REQUEST['type'] = isset($_REQUEST['type']) ? TRIGGER_MULT_EVENT_ENABLED : TRIGGER_MULT_EVENT_DISABLED;

// validate permissions
$discoveryRule = API::DiscoveryRule()->get([
	'output' => ['name', 'itemid', 'hostid'],
	'itemids' => getRequest('parent_discoveryid'),
	'editable' => true
]);
$discoveryRule = reset($discoveryRule);

if (!$discoveryRule) {
	access_deny();
}

$triggerPrototypeIds = getRequest('g_triggerid', []);
if (!is_array($triggerPrototypeIds)) {
	$triggerPrototypeIds = zbx_toArray($triggerPrototypeIds);
}

$triggerPrototypeId = getRequest('triggerid');
if ($triggerPrototypeId !== null) {
	$triggerPrototypeIds[] = $triggerPrototypeId;
}

if ($triggerPrototypeIds) {
	$triggerPrototypes = API::TriggerPrototype()->get([
		'output' => ['triggerid'],
		'triggerids' => $triggerPrototypeIds,
		'editable' => true,
		'preservekeys' => true
	]);

	if ($triggerPrototypes) {
		foreach ($triggerPrototypeIds as $triggerPrototypeId) {
			if (!isset($triggerPrototypes[$triggerPrototypeId])) {
				access_deny();
			}
		}
	}
	else {
		access_deny();
	}
}

$showDisabled = getRequest('showdisabled', 0);
CProfile::update('web.triggers.showdisabled', $showDisabled, PROFILE_TYPE_INT);

/*
 * Actions
 */
$exprAction = null;
if (isset($_REQUEST['add_expression'])) {
	$_REQUEST['expression'] = $_REQUEST['expr_temp'];
	$_REQUEST['expr_temp'] = '';
}
elseif (isset($_REQUEST['and_expression'])) {
	$exprAction = 'and';
}
elseif (isset($_REQUEST['or_expression'])) {
	$exprAction = 'or';
}
elseif (isset($_REQUEST['replace_expression'])) {
	$exprAction = 'r';
}
elseif (getRequest('remove_expression')) {
	$exprAction = 'R';
	$_REQUEST['expr_target_single'] = $_REQUEST['remove_expression'];
}
elseif (isset($_REQUEST['clone']) && isset($_REQUEST['triggerid'])) {
	unset($_REQUEST['triggerid']);
	$_REQUEST['form'] = 'clone';
}
elseif (hasRequest('add') || hasRequest('update')) {
	$trigger = [
		'expression' => getRequest('expression'),
		'description' => getRequest('description'),
		'type' => getRequest('type'),
		'priority' => getRequest('priority'),
		'status' => getRequest('status'),
		'comments' => getRequest('comments'),
		'url' => getRequest('url'),
		'flags' => ZBX_FLAG_DISCOVERY_PROTOTYPE
	];

	if (hasRequest('update')) {
		$trigger['triggerid'] = getRequest('triggerid');
		$result = API::TriggerPrototype()->update($trigger);

		show_messages($result, _('Trigger prototype updated'), _('Cannot update trigger prototype'));
	}
	else {
		$result = API::TriggerPrototype()->create($trigger);

		show_messages($result, _('Trigger prototype added'), _('Cannot add trigger prototype'));
	}

	if ($result) {
		unset($_REQUEST['form']);
		uncheckTableRows(getRequest('parent_discoveryid'));
	}
}
elseif (hasRequest('delete') && hasRequest('triggerid')) {
	$result = API::TriggerPrototype()->delete([getRequest('triggerid')]);

	if ($result) {
		unset($_REQUEST['form'], $_REQUEST['triggerid']);
		uncheckTableRows(getRequest('parent_discoveryid'));
	}
	show_messages($result, _('Trigger prototype deleted'), _('Cannot delete trigger prototype'));
}
elseif (hasRequest('action') && getRequest('action') == 'triggerprototype.massupdate' && hasRequest('massupdate') && hasRequest('g_triggerid')) {
	$triggerIds = getRequest('g_triggerid');
	$visible = getRequest('visible');

	$result = false;
	if (isset($visible['priority'])) {
		$priority = getRequest('priority');

		foreach ($triggerIds as $triggerId) {
			$result = API::TriggerPrototype()->update([
				'triggerid' => $triggerId,
				'priority' => $priority
			]);
			if (!$result) {
				break;
			}
		}
	}
	else {
		$result = true;
	}

	if ($result) {
		unset($_REQUEST['form'], $_REQUEST['g_triggerid']);
		uncheckTableRows(getRequest('parent_discoveryid'));
	}
	show_messages($result, _('Trigger prototypes updated'), _('Cannot update trigger prototypes'));
}
elseif (getRequest('action') && str_in_array(getRequest('action'), ['triggerprototype.massenable', 'triggerprototype.massdisable']) && hasRequest('g_triggerid')) {
	$enable = (getRequest('action') == 'triggerprototype.massenable');
	$status = $enable ? TRIGGER_STATUS_ENABLED : TRIGGER_STATUS_DISABLED;
	$update = [];

	// get requested triggers with permission check
	$dbTriggerPrototypes = API::TriggerPrototype()->get([
		'output' => ['triggerid', 'status'],
		'triggerids' => getRequest('g_triggerid'),
		'editable' => true
	]);

	if ($dbTriggerPrototypes) {
		foreach ($dbTriggerPrototypes as $dbTriggerPrototype) {
			$update[] = [
				'triggerid' => $dbTriggerPrototype['triggerid'],
				'status' => $status
			];
		}

		$result = API::TriggerPrototype()->update($update);
	}
	else {
		$result = true;
	}

	if ($result) {
		uncheckTableRows(getRequest('parent_discoveryid'));
	}

	$updated = count($update);

	$messageSuccess = $enable
		? _n('Trigger prototype enabled', 'Trigger prototypes enabled', $updated)
		: _n('Trigger prototype disabled', 'Trigger prototypes disabled', $updated);
	$messageFailed = $enable
		? _n('Cannot enable trigger prototype', 'Cannot enable trigger prototypes', $updated)
		: _n('Cannot disable trigger prototype', 'Cannot disable trigger prototypes', $updated);

	show_messages($result, $messageSuccess, $messageFailed);
}
elseif (hasRequest('action') && getRequest('action') == 'triggerprototype.massdelete' && hasRequest('g_triggerid')) {
	$result = API::TriggerPrototype()->delete(getRequest('g_triggerid'));

	if ($result) {
		uncheckTableRows(getRequest('parent_discoveryid'));
	}
	show_messages($result, _('Trigger prototypes deleted'), _('Cannot delete trigger prototypes'));
}

/*
 * Display
 */
if (hasRequest('action') && getRequest('action') == 'triggerprototype.massupdateform' && hasRequest('g_triggerid')) {
	$data = getTriggerMassupdateFormData();
	$data['action'] = 'triggerprototype.massupdate';
	$triggersView = new CView('configuration.triggers.massupdate', $data);
	$triggersView->render();
	$triggersView->show();
}
elseif (isset($_REQUEST['form'])) {
	$data = getTriggerFormData($exprAction);
	$data['hostid'] = $discoveryRule['hostid'];

	$triggersView = new CView('configuration.triggers.edit', $data);
	$triggersView->render();
	$triggersView->show();
}
else {
	$sortField = getRequest('sort', CProfile::get('web.'.$page['file'].'.sort', 'description'));
	$sortOrder = getRequest('sortorder', CProfile::get('web.'.$page['file'].'.sortorder', ZBX_SORT_UP));

	CProfile::update('web.'.$page['file'].'.sort', $sortField, PROFILE_TYPE_STR);
	CProfile::update('web.'.$page['file'].'.sortorder', $sortOrder, PROFILE_TYPE_STR);

	$config = select_config();

	$data = [
		'parent_discoveryid' => getRequest('parent_discoveryid'),
		'discovery_rule' => $discoveryRule,
		'hostid' => $discoveryRule['hostid'],
		'showdisabled' => getRequest('showdisabled', 1),
		'triggers' => [],
		'sort' => $sortField,
		'sortorder' => $sortOrder,
		'config' => $config
	];
	CProfile::update('web.triggers.showdisabled', $data['showdisabled'], PROFILE_TYPE_INT);

	// get triggers
	$options = [
		'editable' => true,
		'output' => ['triggerid', $sortField],
		'discoveryids' => $data['parent_discoveryid'],
		'sortfield' => $sortField,
		'limit' => $config['search_limit'] + 1
	];
	if (empty($data['showdisabled'])) {
		$options['filter']['status'] = TRIGGER_STATUS_ENABLED;
	}
	$data['triggers'] = API::TriggerPrototype()->get($options);

	order_result($data['triggers'], $sortField, $sortOrder);

	// paging
	$data['paging'] = getPagingLine($data['triggers'], $sortOrder);

	$data['triggers'] = API::TriggerPrototype()->get([
		'triggerids' => zbx_objectValues($data['triggers'], 'triggerid'),
		'output' => API_OUTPUT_EXTEND,
		'selectHosts' => API_OUTPUT_EXTEND,
		'selectItems' => ['itemid', 'hostid', 'key_', 'type', 'flags', 'status'],
		'selectFunctions' => API_OUTPUT_EXTEND
	]);
	order_result($data['triggers'], $sortField, $sortOrder);

	// get real hosts
	$data['realHosts'] = getParentHostsByTriggers($data['triggers']);

	// render view
	$triggersView = new CView('configuration.trigger.prototype.list', $data);
	$triggersView->render();
	$triggersView->show();
}

require_once dirname(__FILE__).'/include/page_footer.php';
