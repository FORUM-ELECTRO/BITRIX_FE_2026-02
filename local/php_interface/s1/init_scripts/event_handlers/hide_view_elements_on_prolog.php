<?

AddEventHandler('main', 'OnProlog', 'hideViewElementsOnProlog');

function hideViewElementsOnProlog()
{
	$url = $_SERVER['REQUEST_URI'];

	// Скрытие полей и разделов в элементе СП Проектные запросы
	if (str_contains($url, '/type/1204/details/'))
	{
		if (str_contains($url, '/type/1204/details/0/') || str_contains($url, 'copy=1')) {
			// Форма создания
			ob_start();
			?>         
			<style>
				[data-cid="user_wvw94rn0"], /* Решение бренда по запросу - Воронка ПЦ */
				[data-cid="user_lof9fw5f"], /* Решение бренда по запросу - Воронка РП */
				[data-cid="user_gaznse6f"], /* Решение бренда по запросу - Воронка ЗП */
				[data-cid="user_637dr7fv"], /* Решение бренда по запросу - Воронка СПП */
				[data-cid="user_3najt5wc"], /* Решение бренда по запросу - Воронка СВ */
				[data-cid="user_be4yl2gz"] /* Решение бренда по запросу - Воронка РАЭК Проектные цены и спец условия (СУ) */
				{display: none !important;}
			</style>
			<?
			$html = ob_get_clean();
			\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
		}
	}

	// global $USER;
	// if ($USER->IsAdmin())
	// {
	// 	return;
	// }

	// Скрытие полей и разделов в элементе СП Проектные расчёты
	if (str_contains($url, '/type/1406/details/'))
	{
		if (str_contains($url, '/type/1406/details/0/') || str_contains($url, 'copy=1')) {
			// Форма создания
			ob_start();
			?>         
			<style>
				[data-cid="user_7gf59bmm"], /* Решение по расчёту - Воронка КПП */
				[data-cid="user_umlgqlvr"], /* Решение по расчёту - Воронка Производство */
				[data-cid="user_bqovbs00"], /* Решение по расчёту - Воронка НВО */
				[data-cid="user_i42vpp1o"], /* Решение по расчёту - Воронка СВЕТ и Дизайн */
				[data-cid="user_sazxj0lz"] /* Решение по расчёту - Воронка Шинопровод */
				{display: none !important;}
			</style>
			<?
			$html = ob_get_clean();
			\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
		}
	}

	global $USER;
	if ($USER->IsAdmin())
	{
		return;
	}

	// Скрытие "шестерёнки" для настройки представления Список в CRM
	if (str_contains($url, '/crm/lead/list/') || str_contains($url, '/crm/type/') || str_contains($url, '/page/') || str_contains($url, '/crm/deal/category/') || str_contains($url, '/bizproc/processes/') || str_contains($url, '/crm/contact/list/') || str_contains($url, '/crm/company/list/') || str_contains($url, '/crm/invoice/list/') || str_contains($url, '/crm/quote/list/') || str_contains($url, '/services/lists/'))
	{
		ob_start();
		?>         
		<style>
			.main-grid-header .main-grid-interface-settings-icon {display: none !important;}
		</style>
		<?
		$html = ob_get_clean();
		\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
	}

	// Скрытие полей и разделов в элементе СП Проекты
	if (str_contains($url, '/type/185/details/'))
	{
		if (str_contains($url, '/type/185/details/0/')) {
			// Форма создания
			ob_start();
			?>         
			<style>
				[data-cid="UF_CRM_11_1696574333247"], /* ID проекта */
				[data-cid="UF_CRM_11_1697008683"], /* Руководитель */
				[data-cid="UF_CRM_11_1715174463413"] /* Сумма брендов */
				{display: none !important;}
			</style>
			<?
			$html = ob_get_clean();
			\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
		} else {
			// Форма редактирования
			ob_start();
			?>         
			<style>
				[data-cid="user_t4c0sbcy"] /* Основной бренд и контрагент проекта */
				{display: none !important;}
			</style>
			<?
			$html = ob_get_clean();
			\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
		}
	}

	// Скрытие кнопки создания задачи в группах
	if (str_contains($url, '/workgroups/group/475/')  || str_contains($url, '/workgroups/group/553/'))
	{
		ob_start();
		?>         
		<style>
			.tasks-interface-filter-btn-add
			{display: none !important;}
		</style>
		<?
		$html = ob_get_clean();
		\Bitrix\Main\Page\Asset::getInstance()->addString($html, true);
	}
}

?>