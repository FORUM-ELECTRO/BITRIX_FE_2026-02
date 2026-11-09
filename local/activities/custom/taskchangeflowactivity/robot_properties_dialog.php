<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Web\Json;

\Bitrix\Main\UI\Extension::load(['ui.entity-selector', 'tasks.entity-selector']);

/** @var \Bitrix\Bizproc\Activity\PropertiesDialog $dialog */

$map = $dialog->getMap();
$flowField = $map['FlowId'];

$selectedFlowId = null;
$currentValue = $dialog->getCurrentValue($flowField['FieldName']);
if (!empty($currentValue) && is_numeric($currentValue))
{
	$selectedFlowId = (int)$currentValue;
}

$preselectedJs = $selectedFlowId ? Json::encode([['flow', $selectedFlowId]]) : 'undefined';

$keepDeadlineField = $map['KeepDeadline'];
$keepDeadlineRaw = $dialog->getCurrentValue($keepDeadlineField['FieldName']);
$keepDeadlineChecked = ($keepDeadlineRaw === null || $keepDeadlineRaw === '')
	? true
	: CBPHelper::getBool($keepDeadlineRaw);
?>
<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
		<?= htmlspecialcharsbx($flowField['Name']) ?>:
	</span>
	<div id="bizproc-taskchangeflowactivity-flow-id" class="bizproc-type-control-string"></div>
	<input type="hidden" name="<?= htmlspecialcharsbx($flowField['FieldName']) ?>" value="<?= htmlspecialcharsbx((string)$selectedFlowId) ?>">
</div>

<div class="bizproc-automation-popup-settings">
	<span class="bizproc-automation-popup-settings-title bizproc-automation-popup-settings-title-autocomplete">
		<?= htmlspecialcharsbx($map['ModifiedBy']['Name']) ?>:
	</span>
	<?= $dialog->renderFieldControl($map['ModifiedBy']) ?>
</div>

<div class="bizproc-automation-popup-checkbox-item">
	<label class="bizproc-automation-popup-chk-label">
		<input type="hidden" name="<?= htmlspecialcharsbx($keepDeadlineField['FieldName']) ?>" value="N">
		<input type="checkbox" name="<?= htmlspecialcharsbx($keepDeadlineField['FieldName']) ?>" value="Y" class="bizproc-automation-popup-chk" <?= $keepDeadlineChecked ? 'checked' : '' ?>>
		<?= htmlspecialcharsbx($keepDeadlineField['Name']) ?>
	</label>
</div>

<script>
	BX.Event.ready(function ()
	{
		const form = document.forms.namedItem('<?= CUtil::JSEscape($dialog->getFormName()) ?>');
		const container = document.getElementById('bizproc-taskchangeflowactivity-flow-id');
		if (!form || !container)
		{
			return;
		}

		const hiddenInput = form.querySelector('input[name="<?= CUtil::JSEscape($flowField['FieldName']) ?>"]');
		if (!hiddenInput)
		{
			return;
		}

		const selector = new BX.UI.EntitySelector.TagSelector({
			multiple: false,
			events: {
				onTagAdd: (event) => {
					const { tag } = event.getData();
					hiddenInput.value = tag.getId();
				},
				onTagRemove: () => {
					hiddenInput.value = '';
				},
			},
			dialogOptions: {
				width: 400,
				height: 300,
				dropdownMode: true,
				enableSearch: true,
				compactView: true,
				preselectedItems: <?= $preselectedJs ?>,
				entities: [
					{
						id: 'flow',
						options: {
							onlyActive: true,
						},
					},
				],
				preload: true,
			},
		});
		selector.renderTo(container);
	});
</script>
