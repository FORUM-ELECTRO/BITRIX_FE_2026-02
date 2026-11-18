<?php
$arUrlRewrite=array (
  277 => 
  array (
    'CONDITION' => '#^/pub/annuals/([0-9a-zA-Z~]+)/#',
    'RULE' => 'shortCode=$1',
    'ID' => NULL,
    'PATH' => '/pub/annual_summary.php',
    'SORT' => 50,
  ),
  278 => 
  array (
    'CONDITION' => '#^/pub/annual_summary/([0-9a-z\\.]+)/([0-9a-z\\.\\_]+)/\\?user_lang=[a-z]+#',
    'RULE' => 'signedId=$1&signedType=$2',
    'ID' => NULL,
    'PATH' => '/pub/annual_summary.php',
    'SORT' => 60,
  ),
  156 => 
  array (
    'CONDITION' => '#^/site_oo/pub/document/([0-9a-zA-Z_-]+)/([0-9a-zA-Z]+)/([^/]*)#',
    'RULE' => 'id=$1&hash=$2',
    'ID' => 'bitrix:documentgenerator.view',
    'PATH' => '/site_oo/pub/document.php',
    'SORT' => 100,
  ),
  185 => 
  array (
    'CONDITION' => '#^/site_oo/pub/calendar-event/([0-9]+)/([0-9a-zA-Z]+)/?([^/]*)#',
    'RULE' => 'event_id=$1&hash=$2',
    'ID' => 'bitrix:calendar.pub.event',
    'PATH' => '/site_oo/pub/calendar_event.php',
    'SORT' => 100,
  ),
  4 => 
  array (
    'CONDITION' => '#^/docs/pub/(?<hash>[0-9a-f]{32})/(?<action>[0-9a-zA-Z]+)/\\?#',
    'RULE' => 'hash=$1&action=$2&',
    'ID' => 'bitrix:disk.external.link',
    'PATH' => '/docs/pub/index.php',
    'SORT' => 100,
  ),
  48 => 
  array (
    'CONDITION' => '#^/pub/document/([0-9a-zA-Z_-]+)/([0-9a-zA-Z]+)/([^/]*)#',
    'RULE' => 'id=$1&hash=$2',
    'ID' => 'bitrix:documentgenerator.view',
    'PATH' => '/pub/document.php',
    'SORT' => 100,
  ),
  71 => 
  array (
    'CONDITION' => '#^/pub/calendar-event/([0-9]+)/([0-9a-zA-Z]+)/?([^/]*)#',
    'RULE' => 'event_id=$1&hash=$2',
    'ID' => 'bitrix:calendar.pub.event',
    'PATH' => '/pub/calendar_event.php',
    'SORT' => 100,
  ),
  273 => 
  array (
    'CONDITION' => '#^/disk/file/(?<unique_code>[0-9a-zA-Z]{20})/edit(\\/?)#',
    'RULE' => 'action=disk.api.unifiedlinkcontroller.edit&uniqueCode=$1&',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  5 => 
  array (
    'CONDITION' => '#^/disk/(?<action>[0-9a-zA-Z]+)/(?<fileId>[0-9]+)/\\?#',
    'RULE' => 'action=$1&fileId=$2&',
    'ID' => 'bitrix:disk.services',
    'PATH' => '/bitrix/services/disk/index.php',
    'SORT' => 100,
  ),
  155 => 
  array (
    'CONDITION' => '#^/site_oo/pub/form/([0-9a-z_]+?)/([0-9a-z]+?)/.*#',
    'RULE' => 'form_code=$1&sec=$2',
    'ID' => '',
    'PATH' => '/site_oo/pub/form.php',
    'SORT' => 100,
  ),
  274 => 
  array (
    'CONDITION' => '#^/disk/file/(?<unique_code>[0-9a-zA-Z]{20})(\\/?)#',
    'RULE' => 'action=disk.api.unifiedlinkcontroller.view&uniqueCode=$1&',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  186 => 
  array (
    'CONDITION' => '#^/pub/calendar-sharing/([0-9a-zA-Z]+)/?([^/]*)#',
    'RULE' => 'hash=$1',
    'ID' => 'bitrix:calendar.pub.sharing',
    'PATH' => '/pub/calendar_sharing.php',
    'SORT' => 100,
  ),
  240 => 
  array (
    'CONDITION' => '#^/site_oo/online/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1',
    'ID' => '',
    'PATH' => '/site_oo/desktop_app/router.php',
    'SORT' => 100,
  ),
  281 => 
  array (
    'CONDITION' => '#^/extranet/video/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1&videoconf',
    'ID' => 'call:conference',
    'PATH' => '/conference/videoconf.php',
    'SORT' => 100,
  ),
  181 => 
  array (
    'CONDITION' => '#^/site_oo/video/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1&videoconf',
    'ID' => NULL,
    'PATH' => '/site_oo/desktop_app/router.php',
    'SORT' => 100,
  ),
  18 => 
  array (
    'CONDITION' => '#^\\/?\\/mobile/web_mobile_component\\/(.*)\\/.*#',
    'RULE' => 'componentName=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/mobile/webcomponent.php',
    'SORT' => 100,
  ),
  143 => 
  array (
    'CONDITION' => '#^/site_oo/tasks/getfile/(\\d+)/(\\d+)/([^/]+)#',
    'RULE' => 'taskid=$1&fileid=$2&filename=$3',
    'ID' => NULL,
    'PATH' => '/site_oo/tasks/getfile.php',
    'SORT' => 100,
  ),
  196 => 
  array (
    'CONDITION' => '#^/site_oo/timeman/login-history/([0-9]+)/.*#',
    'RULE' => 'user=$1',
    'ID' => 'bitrix:intranet.user.login.history',
    'PATH' => '/site_oo/timeman/login-history/index.php',
    'SORT' => 100,
  ),
  266 => 
  array (
    'CONDITION' => '#^/disk/boards/([0-9]+)/openAttachedDocument#',
    'RULE' => 'action=disk.integration.flipchart.openAttachedDocument&attachedObjectId=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  1 => 
  array (
    'CONDITION' => '#^/pub/pay/([\\w\\W]+)/([0-9a-zA-Z]+)/([^/]*)#',
    'RULE' => 'account_number=$1&hash=$2',
    'ID' => NULL,
    'PATH' => '/pub/payment.php',
    'SORT' => 100,
  ),
  228 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/document_numerators/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.document_numerators.list',
    'PATH' => '/site_oo/crm/configs/document_numerators/index.php',
    'SORT' => 100,
  ),
  255 => 
  array (
    'CONDITION' => '#^/pub/booking/confirmation/([0-9a-z\\.]+)/#',
    'RULE' => 'hash=$1',
    'ID' => 'bitrix:booking.pub.confirm',
    'PATH' => '/pub/booking/confirmation.php',
    'SORT' => 100,
  ),
  47 => 
  array (
    'CONDITION' => '#^/pub/form/([0-9a-z_]+?)/([0-9a-z]+?)/.*#',
    'RULE' => 'form_code=$1&sec=$2',
    'ID' => '',
    'PATH' => '/pub/form.php',
    'SORT' => 100,
  ),
  15 => 
  array (
    'CONDITION' => '#^\\/?\\/mobile/mobile_component\\/(.*)\\/.*#',
    'RULE' => 'componentName=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/mobile/jscomponent.php',
    'SORT' => 100,
  ),
  158 => 
  array (
    'CONDITION' => '#^/site_oo/settings/configs/userconsent/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/configs/userconsent.php',
    'SORT' => 100,
  ),
  19 => 
  array (
    'CONDITION' => '#^/mobile/disk/(?<hash>[0-9]+)/download#',
    'RULE' => 'download=1&objectId=$1',
    'ID' => 'bitrix:mobile.disk.file.detail',
    'PATH' => '/mobile/disk/index.php',
    'SORT' => 100,
  ),
  11 => 
  array (
    'CONDITION' => '#^/online/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1',
    'ID' => NULL,
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  188 => 
  array (
    'CONDITION' => '#^/site_oo/pub/payment-slip/([\\w\\W]+)/#',
    'RULE' => 'signed_payment_id=$1',
    'ID' => 'bitrix:salescenter.pub.payment.slip',
    'PATH' => '/site_oo/pub/payment_slip.php',
    'SORT' => 100,
  ),
  280 => 
  array (
    'CONDITION' => '#^/video/([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1&videoconf',
    'ID' => 'call:conference',
    'PATH' => '/conference/videoconf.php',
    'SORT' => 100,
  ),
  10 => 
  array (
    'CONDITION' => '#^/video([\\.\\-0-9a-zA-Z]+)(/?)([^/]*)#',
    'RULE' => 'alias=$1&videoconf',
    'ID' => NULL,
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  150 => 
  array (
    'CONDITION' => '#^/site_oo/shop/settings/permissions/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.config.permissions',
    'PATH' => '/site_oo/shop/settings/permissions/index.php',
    'SORT' => 100,
  ),
  164 => 
  array (
    'CONDITION' => '#^/site_oo/marketplace/configuration/#',
    'RULE' => '',
    'ID' => 'bitrix:rest.configuration',
    'PATH' => '/site_oo/marketplace/configuration/index.php',
    'SORT' => 100,
  ),
  226 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/deal_category/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.deal_category',
    'PATH' => '/site_oo/crm/configs/deal_category/index.php',
    'SORT' => 100,
  ),
  265 => 
  array (
    'CONDITION' => '#^/extranet/vote-result/([0-9a-z\\.]+)#',
    'RULE' => 'signedAttachId=$1',
    'ID' => 'bitrix:voting.attached.result',
    'PATH' => '/extranet/vote-result/index.php',
    'SORT' => 100,
  ),
  125 => 
  array (
    'CONDITION' => '#^/timeman/login-history/([0-9]+)/.*#',
    'RULE' => 'user=$1',
    'ID' => 'bitrix:intranet.user.login.history',
    'PATH' => '/timeman/login-history/index.php',
    'SORT' => 100,
  ),
  36 => 
  array (
    'CONDITION' => '#^/tasks/getfile/(\\d+)/(\\d+)/([^/]+)#',
    'RULE' => 'taskid=$1&fileid=$2&filename=$3',
    'ID' => NULL,
    'PATH' => '/tasks/getfile.php',
    'SORT' => 100,
  ),
  215 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/mailtemplate/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.mail_template',
    'PATH' => '/site_oo/crm/configs/mailtemplate/index.php',
    'SORT' => 100,
  ),
  219 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/productprops/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.productprops',
    'PATH' => '/site_oo/crm/configs/productprops/index.php',
    'SORT' => 100,
  ),
  267 => 
  array (
    'CONDITION' => '#^/disk/boards/([0-9]+)/openDocument#',
    'RULE' => 'action=disk.integration.flipchart.openDocument&fileId=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  268 => 
  array (
    'CONDITION' => '#^/disk/boards/([0-9]+)/openAttached#',
    'RULE' => 'action=disk.integration.flipchart.openAttachedDocument&attachedObjectId=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  106 => 
  array (
    'CONDITION' => '#^/crm/configs/document_numerators/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.document_numerators.list',
    'PATH' => '/crm/configs/document_numerators/index.php',
    'SORT' => 100,
  ),
  176 => 
  array (
    'CONDITION' => '#^/site_oo/mobile/knowledge/group/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.pub',
    'PATH' => '/site_oo/mobile/knowledge/group/index.php',
    'SORT' => 100,
  ),
  190 => 
  array (
    'CONDITION' => '#^/site_oo/shop/documents-catalog/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.catalog.controller',
    'PATH' => '/site_oo/shop/documents-catalog/index.php',
    'SORT' => 100,
  ),
  205 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/automation/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.automation',
    'PATH' => '/site_oo/crm/configs/automation/index.php',
    'SORT' => 100,
  ),
  279 => 
  array (
    'CONDITION' => '#^/extranet/task/comments/([0-9]+)#',
    'RULE' => 'taskId=$1',
    'ID' => NULL,
    'PATH' => '/extranet/tasks/comments.php',
    'SORT' => 100,
  ),
  7 => 
  array (
    'CONDITION' => '#^/stssync/contacts_extranet_emp/#',
    'RULE' => '',
    'ID' => 'bitrix:stssync.server',
    'PATH' => '/bitrix/services/stssync/contacts_extranet_emp/index.php',
    'SORT' => 100,
  ),
  173 => 
  array (
    'CONDITION' => '#^/site_oo/shop/import/instagram/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.order.import.instagram',
    'PATH' => '/site_oo/shop/import/instagram/index.php',
    'SORT' => 100,
  ),
  191 => 
  array (
    'CONDITION' => '#^/site_oo/shop/documents-stores/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.store.entity.controller',
    'PATH' => '/site_oo/shop/documents-stores/index.php',
    'SORT' => 100,
  ),
  212 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/locations/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.locations',
    'PATH' => '/site_oo/crm/configs/locations/index.php',
    'SORT' => 100,
  ),
  223 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/exclusion/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/crm/configs/exclusion/index.php',
    'SORT' => 100,
  ),
  225 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/mycompany/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.company',
    'PATH' => '/site_oo/crm/configs/mycompany/index.php',
    'SORT' => 100,
  ),
  239 => 
  array (
    'CONDITION' => '#^/site_oo/marketing/config/role/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/marketing/config/role.php',
    'SORT' => 100,
  ),
  50 => 
  array (
    'CONDITION' => '#^/settings/configs/userconsent/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/configs/userconsent.php',
    'SORT' => 100,
  ),
  210 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/currency/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.currency',
    'PATH' => '/site_oo/crm/configs/currency/index.php',
    'SORT' => 100,
  ),
  258 => 
  array (
    'CONDITION' => '#^/bi/dashboard/detail/([0-9]+)/#',
    'RULE' => 'dashboardId=$1',
    'ID' => 'bitrix:biconnector.apachesuperset.dashboard.detail',
    'PATH' => '/bi/dashboard/detail/index.php',
    'SORT' => 100,
  ),
  264 => 
  array (
    'CONDITION' => '#^/extranet/call/detail/([0-9]+)#',
    'RULE' => 'callId=$1',
    'ID' => 'bitrix:call',
    'PATH' => '/extranet/call/index.php',
    'SORT' => 100,
  ),
  17 => 
  array (
    'CONDITION' => '#^\\/?\\/mobile/jn/(.*)\\/(.*)\\/.*#',
    'RULE' => 'componentName=$2&namespace=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/mobile/jscomponent.php',
    'SORT' => 100,
  ),
  133 => 
  array (
    'CONDITION' => '#^/docs/spravochnik-sotrudnika/#',
    'RULE' => '',
    'ID' => 'bitrix:disk.common',
    'PATH' => '/docs/spravochnik-sotrudnika/index.php',
    'SORT' => 100,
  ),
  217 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/measure/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.measure',
    'PATH' => '/site_oo/crm/configs/measure/index.php',
    'SORT' => 100,
  ),
  235 => 
  array (
    'CONDITION' => '#^/site_oo/marketing/blacklist/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/marketing/blacklist.php',
    'SORT' => 100,
  ),
  119 => 
  array (
    'CONDITION' => '#^/extranet/workgroups/create/#',
    'RULE' => NULL,
    'ID' => 'bitrix:extranet.group_create',
    'PATH' => '/extranet/workgroups/create/index.php',
    'SORT' => 100,
  ),
  120 => 
  array (
    'CONDITION' => '#^/extranet/contacts/personal/#',
    'RULE' => NULL,
    'ID' => 'bitrix:socialnetwork_user',
    'PATH' => '/extranet/contacts/personal.php',
    'SORT' => 100,
  ),
  132 => 
  array (
    'CONDITION' => '#^/pub/payment-slip/([\\w\\W]+)/#',
    'RULE' => 'signed_payment_id=$1',
    'ID' => 'bitrix:salescenter.pub.payment.slip',
    'PATH' => '/pub/payment_slip.php',
    'SORT' => 100,
  ),
  204 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/fields/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.fields',
    'PATH' => '/site_oo/crm/configs/fields/index.php',
    'SORT' => 100,
  ),
  214 => 
  array (
    'CONDITION' => '#^/site_oo/crm/reports/report/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.report',
    'PATH' => '/site_oo/crm/reports/report/index.php',
    'SORT' => 100,
  ),
  216 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/exch1c/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.exch1c',
    'PATH' => '/site_oo/crm/configs/exch1c/index.php',
    'SORT' => 100,
  ),
  218 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/volume/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.volume',
    'PATH' => '/site_oo/crm/configs/volume/index.php',
    'SORT' => 100,
  ),
  220 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/preset/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.preset',
    'PATH' => '/site_oo/crm/configs/preset/index.php',
    'SORT' => 100,
  ),
  234 => 
  array (
    'CONDITION' => '#^/site_oo/marketing/template/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/marketing/template.php',
    'SORT' => 100,
  ),
  20 => 
  array (
    'CONDITION' => '#^\\/?\\/mobileapp/jn\\/(.*)\\/.*#',
    'RULE' => 'componentName=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/mobileapp/jn.php',
    'SORT' => 100,
  ),
  26 => 
  array (
    'CONDITION' => '#^/marketplace/configuration/#',
    'RULE' => '',
    'ID' => 'bitrix:rest.configuration',
    'PATH' => '/marketplace/configuration/index.php',
    'SORT' => 100,
  ),
  104 => 
  array (
    'CONDITION' => '#^/crm/configs/deal_category/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.deal_category',
    'PATH' => '/crm/configs/deal_category/index.php',
    'SORT' => 100,
  ),
  6 => 
  array (
    'CONDITION' => '#^/stssync/contacts_extranet/#',
    'RULE' => '',
    'ID' => 'bitrix:stssync.server',
    'PATH' => '/bitrix/services/stssync/contacts_extranet/index.php',
    'SORT' => 100,
  ),
  9 => 
  array (
    'CONDITION' => '#^/stssync/calendar_extranet/#',
    'RULE' => '',
    'ID' => 'bitrix:stssync.server',
    'PATH' => '/bitrix/services/stssync/calendar_extranet/index.php',
    'SORT' => 100,
  ),
  149 => 
  array (
    'CONDITION' => '#^/site_oo/bizproc/processes/#',
    'RULE' => '',
    'ID' => 'bitrix:lists',
    'PATH' => '/site_oo/bizproc/processes/index.php',
    'SORT' => 100,
  ),
  161 => 
  array (
    'CONDITION' => '#^/site_oo/marketplace/local/#',
    'RULE' => '',
    'ID' => 'bitrix:rest.marketplace.localapp',
    'PATH' => '/site_oo/marketplace/local/index.php',
    'SORT' => 100,
  ),
  169 => 
  array (
    'CONDITION' => '#^/site_oo/timeman/schedules/#',
    'RULE' => '',
    'ID' => 'bitrix:timeman.schedules',
    'PATH' => '/site_oo/timeman/schedules.php',
    'SORT' => 100,
  ),
  207 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/perms/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.perms',
    'PATH' => '/site_oo/crm/configs/perms/index.php',
    'SORT' => 100,
  ),
  233 => 
  array (
    'CONDITION' => '#^/site_oo/marketing/segment/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/marketing/segment.php',
    'SORT' => 100,
  ),
  236 => 
  array (
    'CONDITION' => '#^/site_oo/marketing/contact/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/marketing/contact.php',
    'SORT' => 100,
  ),
  241 => 
  array (
    'CONDITION' => '#^/site_oo/online/(/?)([^/]*)#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/desktop_app/router.php',
    'SORT' => 100,
  ),
  246 => 
  array (
    'CONDITION' => '#^/sign/link/member/([0-9]+)/#',
    'RULE' => 'memberId=$1',
    'ID' => '',
    'PATH' => '/sign/link.php',
    'SORT' => 100,
  ),
  271 => 
  array (
    'CONDITION' => '#^/shop/settings/permissions/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.store.entity.controller',
    'PATH' => '/shop/settings/permissions/index.php',
    'SORT' => 100,
  ),
  93 => 
  array (
    'CONDITION' => '#^/crm/configs/mailtemplate/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.mail_template',
    'PATH' => '/crm/configs/mailtemplate/index.php',
    'SORT' => 100,
  ),
  97 => 
  array (
    'CONDITION' => '#^/crm/configs/productprops/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.productprops',
    'PATH' => '/crm/configs/productprops/index.php',
    'SORT' => 100,
  ),
  122 => 
  array (
    'CONDITION' => '#^/extranet/marketplace/app/#',
    'RULE' => NULL,
    'ID' => 'bitrix:app.layout',
    'PATH' => '/extranet/marketplace/app/index.php',
    'SORT' => 100,
  ),
  137 => 
  array (
    'CONDITION' => '#^/site_oo/company/personal/#',
    'RULE' => '',
    'ID' => 'bitrix:socialnetwork_user',
    'PATH' => '/site_oo/company/personal.php',
    'SORT' => 100,
  ),
  163 => 
  array (
    'CONDITION' => '#^/site_oo/marketplace/hook/#',
    'RULE' => '',
    'ID' => 'bitrix:rest.hook',
    'PATH' => '/site_oo/marketplace/hook/index.php',
    'SORT' => 100,
  ),
  167 => 
  array (
    'CONDITION' => '#^/site_oo/timeman/worktime/#',
    'RULE' => '',
    'ID' => 'bitrix:timeman.worktime',
    'PATH' => '/site_oo/timeman/worktime.php',
    'SORT' => 100,
  ),
  168 => 
  array (
    'CONDITION' => '#^/site_oo/timeman/settings/#',
    'RULE' => '',
    'ID' => 'bitrix:timeman.settings',
    'PATH' => '/site_oo/timeman/settings.php',
    'SORT' => 100,
  ),
  171 => 
  array (
    'CONDITION' => '#^/site_oo/shop/buyer_group/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.order.buyer_group',
    'PATH' => '/site_oo/shop/buyer_group/index.php',
    'SORT' => 100,
  ),
  177 => 
  array (
    'CONDITION' => '#^/site_oo/mobile/knowledge/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.pub',
    'PATH' => '/site_oo/mobile/knowledge/index.php',
    'SORT' => 100,
  ),
  231 => 
  array (
    'CONDITION' => '#^/site_oo/marketing/letter/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/marketing/letter.php',
    'SORT' => 100,
  ),
  238 => 
  array (
    'CONDITION' => '#^/site_oo/marketing/toloka/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/marketing/toloka.php',
    'SORT' => 100,
  ),
  269 => 
  array (
    'CONDITION' => '#^/disk/boards/([0-9]+)/open#',
    'RULE' => 'action=disk.integration.flipchart.openDocument&fileId=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/main/ajax.php',
    'SORT' => 100,
  ),
  270 => 
  array (
    'CONDITION' => '#^/vote-result/([0-9a-z\\.]+)#',
    'RULE' => 'signedAttachId=$1',
    'ID' => NULL,
    'PATH' => '/vote-result/index.php',
    'SORT' => 100,
  ),
  27 => 
  array (
    'CONDITION' => '#^/bitrix/services/ymarket/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/bitrix/services/ymarket/index.php',
    'SORT' => 100,
  ),
  127 => 
  array (
    'CONDITION' => '#^/marketing/master-yandex/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/marketing/master-yandex.php',
    'SORT' => 100,
  ),
  136 => 
  array (
    'CONDITION' => '#^/site_oo/company/gallery/#',
    'RULE' => '',
    'ID' => 'bitrix:photogallery_user',
    'PATH' => '/site_oo/company/gallery/index.php',
    'SORT' => 100,
  ),
  162 => 
  array (
    'CONDITION' => '#^/site_oo/marketplace/app/#',
    'RULE' => '',
    'ID' => 'bitrix:app.layout',
    'PATH' => '/site_oo/marketplace/app/index.php',
    'SORT' => 100,
  ),
  174 => 
  array (
    'CONDITION' => '#^/site_oo/knowledge/group/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.pub',
    'PATH' => '/site_oo/knowledge/group/index.php',
    'SORT' => 100,
  ),
  211 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/tax/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.tax',
    'PATH' => '/site_oo/crm/configs/tax/index.php',
    'SORT' => 100,
  ),
  230 => 
  array (
    'CONDITION' => '#^/site_oo/timeman/meeting/#',
    'RULE' => '',
    'ID' => 'bitrix:meetings',
    'PATH' => '/site_oo/timeman/meeting/index.php',
    'SORT' => 100,
  ),
  74 => 
  array (
    'CONDITION' => '#^/shop/documents-catalog/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.catalog.controller',
    'PATH' => '/shop/documents-catalog/index.php',
    'SORT' => 100,
  ),
  62 => 
  array (
    'CONDITION' => '#^/mobile/knowledge/group/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.pub',
    'PATH' => '/mobile/knowledge/group/index.php',
    'SORT' => 100,
  ),
  8 => 
  array (
    'CONDITION' => '#^/stssync/tasks_extranet/#',
    'RULE' => '',
    'ID' => 'bitrix:stssync.server',
    'PATH' => '/bitrix/services/stssync/tasks_extranet/index.php',
    'SORT' => 100,
  ),
  16 => 
  array (
    'CONDITION' => '#^\\/?\\/mobile/jn\\/(.*)\\/.*#',
    'RULE' => 'componentName=$1',
    'ID' => NULL,
    'PATH' => '/bitrix/services/mobile/jscomponent.php',
    'SORT' => 100,
  ),
  83 => 
  array (
    'CONDITION' => '#^/crm/configs/automation/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.automation',
    'PATH' => '/crm/configs/automation/index.php',
    'SORT' => 100,
  ),
  140 => 
  array (
    'CONDITION' => '#^/site_oo/services/lists/#',
    'RULE' => '',
    'ID' => 'bitrix:lists',
    'PATH' => '/site_oo/services/lists/index.php',
    'SORT' => 100,
  ),
  170 => 
  array (
    'CONDITION' => '#^/site_oo/shop/orderform/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.order.matcher',
    'PATH' => '/site_oo/shop/orderform/index.php',
    'SORT' => 100,
  ),
  187 => 
  array (
    'CONDITION' => '#^/site_oo/calendar/rooms/#',
    'RULE' => '',
    'ID' => 'bitrix:calender',
    'PATH' => '/site_oo/calendar/rooms.php',
    'SORT' => 100,
  ),
  189 => 
  array (
    'CONDITION' => '#^/site_oo/shop/documents/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.store.document',
    'PATH' => '/site_oo/shop/documents/index.php',
    'SORT' => 100,
  ),
  197 => 
  array (
    'CONDITION' => '#^/site_oo/agent_contract/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.agent.contract.controller',
    'PATH' => '/site_oo/agent_contract/index.php',
    'SORT' => 100,
  ),
  206 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/bp/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.bp',
    'PATH' => '/site_oo/crm/configs/bp/index.php',
    'SORT' => 100,
  ),
  213 => 
  array (
    'CONDITION' => '#^/site_oo/crm/configs/ps/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.ps',
    'PATH' => '/site_oo/crm/configs/ps/index.php',
    'SORT' => 100,
  ),
  254 => 
  array (
    'CONDITION' => '#^/booking/detail/([0-9]+)#',
    'RULE' => 'id=$1',
    'ID' => 'bitrix:booking.booking.detail',
    'PATH' => '/booking/detail.php',
    'SORT' => 100,
  ),
  123 => 
  array (
    'CONDITION' => '#^/extranet/mobile/webdav#',
    'RULE' => NULL,
    'ID' => 'bitrix:mobile.webdav.file.list',
    'PATH' => '/extranet/mobile/webdav/index.php',
    'SORT' => 100,
  ),
  90 => 
  array (
    'CONDITION' => '#^/crm/configs/locations/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.locations',
    'PATH' => '/crm/configs/locations/index.php',
    'SORT' => 100,
  ),
  124 => 
  array (
    'CONDITION' => '#^/shop/documents-stores/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.store.entity.controller',
    'PATH' => '/shop/documents-stores/index.php',
    'SORT' => 100,
  ),
  117 => 
  array (
    'CONDITION' => '#^/marketing/config/role/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/marketing/config/role.php',
    'SORT' => 100,
  ),
  101 => 
  array (
    'CONDITION' => '#^/crm/configs/exclusion/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/crm/configs/exclusion/index.php',
    'SORT' => 100,
  ),
  103 => 
  array (
    'CONDITION' => '#^/crm/configs/mycompany/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.company',
    'PATH' => '/crm/configs/mycompany/index.php',
    'SORT' => 100,
  ),
  138 => 
  array (
    'CONDITION' => '#^/site_oo/about/gallery/#',
    'RULE' => '',
    'ID' => 'bitrix:photogallery',
    'PATH' => '/site_oo/about/gallery/index.php',
    'SORT' => 100,
  ),
  142 => 
  array (
    'CONDITION' => '#^/site_oo/services/idea/#',
    'RULE' => '',
    'ID' => 'bitrix:idea',
    'PATH' => '/site_oo/services/idea/index.php',
    'SORT' => 100,
  ),
  151 => 
  array (
    'CONDITION' => '#^/site_oo/shop/settings/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.admin.page.controller',
    'PATH' => '/site_oo/shop/settings/index.php',
    'SORT' => 100,
  ),
  195 => 
  array (
    'CONDITION' => '#^/site_oo/shop/terminal/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.terminal.payment.controller',
    'PATH' => '/site_oo/terminal/index.php',
    'SORT' => 100,
  ),
  232 => 
  array (
    'CONDITION' => '#^/site_oo/marketing/ads/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/marketing/ads.php',
    'SORT' => 100,
  ),
  275 => 
  array (
    'CONDITION' => '#^/task/comments/([0-9]+)#',
    'RULE' => 'taskId=$1',
    'ID' => NULL,
    'PATH' => '/tasks/comments.php',
    'SORT' => 100,
  ),
  121 => 
  array (
    'CONDITION' => '#^/extranet/marketplace/#',
    'RULE' => NULL,
    'ID' => 'bitrix:rest.marketplace',
    'PATH' => '/extranet/marketplace/index.php',
    'SORT' => 100,
  ),
  2 => 
  array (
    'CONDITION' => '#^/stssync/contacts_crm/#',
    'RULE' => '',
    'ID' => 'bitrix:stssync.server',
    'PATH' => '/bitrix/services/stssync/contacts_crm/index.php',
    'SORT' => 100,
  ),
  88 => 
  array (
    'CONDITION' => '#^/crm/configs/currency/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.currency',
    'PATH' => '/crm/configs/currency/index.php',
    'SORT' => 100,
  ),
  141 => 
  array (
    'CONDITION' => '#^/site_oo/services/faq/#',
    'RULE' => '',
    'ID' => 'bitrix:support.faq',
    'PATH' => '/site_oo/services/faq/index.php',
    'SORT' => 100,
  ),
  192 => 
  array (
    'CONDITION' => '#^/site_oo/shop/catalog/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.productcard.controller',
    'PATH' => '/site_oo//shop/catalog/index.php',
    'SORT' => 100,
  ),
  194 => 
  array (
    'CONDITION' => '#^/site_oo/crm/terminal/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.terminal.payment.controller',
    'PATH' => '/site_oo/terminal/index.php',
    'SORT' => 100,
  ),
  224 => 
  array (
    'CONDITION' => '#^/site_oo/crm/tracking/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/crm/tracking/index.php',
    'SORT' => 100,
  ),
  227 => 
  array (
    'CONDITION' => '#^/site_oo/crm/activity/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.activity',
    'PATH' => '/site_oo/crm/activity/index.php',
    'SORT' => 100,
  ),
  237 => 
  array (
    'CONDITION' => '#^/site_oo/marketing/rc/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/marketing/rc.php',
    'SORT' => 100,
  ),
  118 => 
  array (
    'CONDITION' => '#^/extranet/workgroups/#',
    'RULE' => NULL,
    'ID' => 'bitrix:socialnetwork_group',
    'PATH' => '/extranet/workgroups/index.php',
    'SORT' => 100,
  ),
  95 => 
  array (
    'CONDITION' => '#^/crm/configs/measure/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.measure',
    'PATH' => '/crm/configs/measure/index.php',
    'SORT' => 100,
  ),
  113 => 
  array (
    'CONDITION' => '#^/marketing/blacklist/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/marketing/blacklist.php',
    'SORT' => 100,
  ),
  148 => 
  array (
    'CONDITION' => '#^/site_oo/docs/manage/#',
    'RULE' => '',
    'ID' => 'bitrix:disk.common',
    'PATH' => '/site_oo/docs/manage/index.php',
    'SORT' => 100,
  ),
  153 => 
  array (
    'CONDITION' => '#^/site_oo/shop/stores/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.start',
    'PATH' => '/site_oo/shop/stores/index.php',
    'SORT' => 100,
  ),
  154 => 
  array (
    'CONDITION' => '#^/site_oo/shop/orders/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.order',
    'PATH' => '/site_oo/shop/orders/index.php',
    'SORT' => 100,
  ),
  159 => 
  array (
    'CONDITION' => '#^/site_oo/marketplace/#',
    'RULE' => '',
    'ID' => 'bitrix:rest.marketplace',
    'PATH' => '/site_oo/marketplace/index.php',
    'SORT' => 100,
  ),
  199 => 
  array (
    'CONDITION' => '#^/site_oo/crm/contact/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.contact',
    'PATH' => '/site_oo/crm/contact/index.php',
    'SORT' => 100,
  ),
  200 => 
  array (
    'CONDITION' => '#^/site_oo/crm/company/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.company',
    'PATH' => '/site_oo/crm/company/index.php',
    'SORT' => 100,
  ),
  203 => 
  array (
    'CONDITION' => '#^/site_oo/crm/invoice/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.invoice',
    'PATH' => '/site_oo/crm/invoice/index.php',
    'SORT' => 100,
  ),
  208 => 
  array (
    'CONDITION' => '#^/site_oo/crm/product/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.product',
    'PATH' => '/site_oo/crm/product/index.php',
    'SORT' => 100,
  ),
  209 => 
  array (
    'CONDITION' => '#^/site_oo/crm/catalog/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.catalog.controller',
    'PATH' => '/site_oo/crm/catalog/index.php',
    'SORT' => 100,
  ),
  221 => 
  array (
    'CONDITION' => '#^/site_oo/crm/webform/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/crm/webform/index.php',
    'SORT' => 100,
  ),
  263 => 
  array (
    'CONDITION' => '#^/call/detail/([0-9]+)#',
    'RULE' => 'callId=$1',
    'ID' => 'bitrix:call',
    'PATH' => '/call/index.php',
    'SORT' => 100,
  ),
  98 => 
  array (
    'CONDITION' => '#^/crm/configs/preset/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.preset',
    'PATH' => '/crm/configs/preset/index.php',
    'SORT' => 100,
  ),
  94 => 
  array (
    'CONDITION' => '#^/crm/configs/exch1c/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.exch1c',
    'PATH' => '/crm/configs/exch1c/index.php',
    'SORT' => 100,
  ),
  96 => 
  array (
    'CONDITION' => '#^/crm/configs/volume/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.volume',
    'PATH' => '/crm/configs/volume/index.php',
    'SORT' => 100,
  ),
  92 => 
  array (
    'CONDITION' => '#^/crm/reports/report/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.report',
    'PATH' => '/crm/reports/report/index.php',
    'SORT' => 100,
  ),
  112 => 
  array (
    'CONDITION' => '#^/marketing/template/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/marketing/template.php',
    'SORT' => 100,
  ),
  82 => 
  array (
    'CONDITION' => '#^/crm/configs/fields/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.fields',
    'PATH' => '/crm/configs/fields/index.php',
    'SORT' => 100,
  ),
  139 => 
  array (
    'CONDITION' => '#^/site_oo/workgroups/#',
    'RULE' => '',
    'ID' => 'bitrix:socialnetwork_group',
    'PATH' => '/site_oo/workgroups/index.php',
    'SORT' => 100,
  ),
  147 => 
  array (
    'CONDITION' => '#^/site_oo/docs/shared#',
    'RULE' => '',
    'ID' => 'bitrix:disk.common',
    'PATH' => '/site_oo/docs/shared/index.php',
    'SORT' => 100,
  ),
  172 => 
  array (
    'CONDITION' => '#^/site_oo/shop/buyer/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.order.buyer',
    'PATH' => '/site_oo/shop/buyer/index.php',
    'SORT' => 100,
  ),
  183 => 
  array (
    'CONDITION' => '#^/site_oo/conference/#',
    'RULE' => '',
    'ID' => 'bitrix:im.conference.center',
    'PATH' => '/site_oo/conference/index.php',
    'SORT' => 100,
  ),
  222 => 
  array (
    'CONDITION' => '#^/site_oo/crm/button/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/site_oo/crm/button/index.php',
    'SORT' => 100,
  ),
  272 => 
  array (
    'CONDITION' => '#^/bi/unused_elements/#',
    'RULE' => '',
    'ID' => 'bitrix:biconnector.apachesuperset.workspace_analytic.controller',
    'PATH' => '/bi/unused_elements/index.php',
    'SORT' => 100,
  ),
  114 => 
  array (
    'CONDITION' => '#^/marketing/contact/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/marketing/contact.php',
    'SORT' => 100,
  ),
  111 => 
  array (
    'CONDITION' => '#^/marketing/segment/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/marketing/segment.php',
    'SORT' => 100,
  ),
  55 => 
  array (
    'CONDITION' => '#^/timeman/schedules/#',
    'RULE' => '',
    'ID' => 'bitrix:timeman.schedules',
    'PATH' => '/timeman/schedules.php',
    'SORT' => 100,
  ),
  85 => 
  array (
    'CONDITION' => '#^/crm/configs/perms/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.perms',
    'PATH' => '/crm/configs/perms/index.php',
    'SORT' => 100,
  ),
  23 => 
  array (
    'CONDITION' => '#^/marketplace/local/#',
    'RULE' => '',
    'ID' => 'bitrix:rest.marketplace.localapp',
    'PATH' => '/marketplace/local/index.php',
    'SORT' => 100,
  ),
  12 => 
  array (
    'CONDITION' => '#^/online/(/?)([^/]*)#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/desktop_app/router.php',
    'SORT' => 100,
  ),
  42 => 
  array (
    'CONDITION' => '#^/bizproc/processes/#',
    'RULE' => '',
    'ID' => 'bitrix:lists',
    'PATH' => '/bizproc/processes/index.php',
    'SORT' => 100,
  ),
  146 => 
  array (
    'CONDITION' => '#^/site_oo/docs/sale/#',
    'RULE' => '',
    'ID' => 'bitrix:disk.common',
    'PATH' => '/site_oo/docs/sale/index.php',
    'SORT' => 100,
  ),
  175 => 
  array (
    'CONDITION' => '#^/site_oo/knowledge/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.pub',
    'PATH' => '/site_oo/knowledge/index.php',
    'SORT' => 100,
  ),
  202 => 
  array (
    'CONDITION' => '#^/site_oo/crm/quote/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.quote',
    'PATH' => '/site_oo/crm/quote/index.php',
    'SORT' => 100,
  ),
  30 => 
  array (
    'CONDITION' => '#^/company/personal/#',
    'RULE' => '',
    'ID' => 'bitrix:socialnetwork_user',
    'PATH' => '/company/personal.php',
    'SORT' => 100,
  ),
  53 => 
  array (
    'CONDITION' => '#^/timeman/worktime/#',
    'RULE' => '',
    'ID' => 'bitrix:timeman.worktime',
    'PATH' => '/timeman/worktime.php',
    'SORT' => 100,
  ),
  57 => 
  array (
    'CONDITION' => '#^/shop/buyer_group/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.order.buyer_group',
    'PATH' => '/shop/buyer_group/index.php',
    'SORT' => 100,
  ),
  54 => 
  array (
    'CONDITION' => '#^/timeman/settings/#',
    'RULE' => '',
    'ID' => 'bitrix:timeman.settings',
    'PATH' => '/timeman/settings.php',
    'SORT' => 100,
  ),
  0 => 
  array (
    'CONDITION' => '#^/stssync/calendar/#',
    'RULE' => '',
    'ID' => 'bitrix:stssync.server',
    'PATH' => '/bitrix/services/stssync/calendar/index.php',
    'SORT' => 100,
  ),
  25 => 
  array (
    'CONDITION' => '#^/marketplace/hook/#',
    'RULE' => '',
    'ID' => 'bitrix:rest.hook',
    'PATH' => '/marketplace/hook/index.php',
    'SORT' => 100,
  ),
  109 => 
  array (
    'CONDITION' => '#^/marketing/letter/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/marketing/letter.php',
    'SORT' => 100,
  ),
  116 => 
  array (
    'CONDITION' => '#^/marketing/toloka/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/marketing/toloka.php',
    'SORT' => 100,
  ),
  13 => 
  array (
    'CONDITION' => '#^/stssync/contacts/#',
    'RULE' => '',
    'ID' => 'bitrix:stssync.server',
    'PATH' => '/bitrix/services/stssync/contacts/index.php',
    'SORT' => 100,
  ),
  63 => 
  array (
    'CONDITION' => '#^/mobile/knowledge/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.pub',
    'PATH' => '/mobile/knowledge/index.php',
    'SORT' => 100,
  ),
  144 => 
  array (
    'CONDITION' => '#^/site_oo/docs/pub/#',
    'RULE' => '',
    'ID' => 'bitrix:disk.external.link',
    'PATH' => '/site_oo/docs/pub/extlinks.php',
    'SORT' => 100,
  ),
  145 => 
  array (
    'CONDITION' => '#^/site_oo//docs/all#',
    'RULE' => '',
    'ID' => 'bitrix:disk.aggregator',
    'PATH' => '/site_oo/docs/index.php',
    'SORT' => 100,
  ),
  178 => 
  array (
    'CONDITION' => '#^/site_oo/kb/group/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.start',
    'PATH' => '/site_oo/kb/group/index.php',
    'SORT' => 100,
  ),
  182 => 
  array (
    'CONDITION' => '#^/site_oo/crm/type/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.router',
    'PATH' => '/site_oo/crm/type/index.php',
    'SORT' => 100,
  ),
  193 => 
  array (
    'CONDITION' => '#^/site_oo/terminal/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.terminal.payment.controller',
    'PATH' => '/site_oo/terminal/index.php',
    'SORT' => 100,
  ),
  198 => 
  array (
    'CONDITION' => '#^/site_oo/crm/lead/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.lead',
    'PATH' => '/site_oo/crm/lead/index.php',
    'SORT' => 100,
  ),
  201 => 
  array (
    'CONDITION' => '#^/site_oo/crm/deal/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.deal',
    'PATH' => '/site_oo/crm/deal/index.php',
    'SORT' => 100,
  ),
  89 => 
  array (
    'CONDITION' => '#^/crm/configs/tax/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.tax',
    'PATH' => '/crm/configs/tax/index.php',
    'SORT' => 100,
  ),
  60 => 
  array (
    'CONDITION' => '#^/knowledge/group/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.pub',
    'PATH' => '/knowledge/group/index.php',
    'SORT' => 100,
  ),
  29 => 
  array (
    'CONDITION' => '#^/company/gallery/#',
    'RULE' => '',
    'ID' => 'bitrix:photogallery_user',
    'PATH' => '/company/gallery/index.php',
    'SORT' => 100,
  ),
  108 => 
  array (
    'CONDITION' => '#^/timeman/meeting/#',
    'RULE' => '',
    'ID' => 'bitrix:meetings',
    'PATH' => '/timeman/meeting/index.php',
    'SORT' => 100,
  ),
  24 => 
  array (
    'CONDITION' => '#^/marketplace/app/#',
    'RULE' => '',
    'ID' => 'bitrix:app.layout',
    'PATH' => '/marketplace/app/index.php',
    'SORT' => 100,
  ),
  247 => 
  array (
    'CONDITION' => '#^/automation/type/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.router',
    'PATH' => '/automation/type/index.php',
    'SORT' => 100,
  ),
  73 => 
  array (
    'CONDITION' => '#^/shop/documents/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.store.document',
    'PATH' => '/shop/documents/index.php',
    'SORT' => 100,
  ),
  33 => 
  array (
    'CONDITION' => '#^/services/lists/#',
    'RULE' => '',
    'ID' => 'bitrix:lists',
    'PATH' => '/services/lists/index.php',
    'SORT' => 100,
  ),
  72 => 
  array (
    'CONDITION' => '#^/calendar/rooms/#',
    'RULE' => '',
    'ID' => 'bitrix:calender',
    'PATH' => '/calendar/rooms.php',
    'SORT' => 100,
  ),
  84 => 
  array (
    'CONDITION' => '#^/crm/configs/bp/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.bp',
    'PATH' => '/crm/configs/bp/index.php',
    'SORT' => 100,
  ),
  56 => 
  array (
    'CONDITION' => '#^/shop/orderform/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.order.matcher',
    'PATH' => '/shop/orderform/index.php',
    'SORT' => 100,
  ),
  91 => 
  array (
    'CONDITION' => '#^/crm/configs/ps/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.config.ps',
    'PATH' => '/crm/configs/ps/index.php',
    'SORT' => 100,
  ),
  135 => 
  array (
    'CONDITION' => '#^/agent_contract/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.agent.contract.controller',
    'PATH' => '/agent_contract/index.php',
    'SORT' => 100,
  ),
  160 => 
  array (
    'CONDITION' => '#^/site_oo/market/#',
    'RULE' => '',
    'ID' => 'bitrix:market',
    'PATH' => '/site_oo/market/index.php',
    'SORT' => 100,
  ),
  165 => 
  array (
    'CONDITION' => '#^/site_oo/devops/#',
    'RULE' => '',
    'ID' => 'bitrix:rest.devops',
    'PATH' => '/site_oo/devops/index.php',
    'SORT' => 100,
  ),
  229 => 
  array (
    'CONDITION' => '#^/site_oo/crm/ml/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.ml',
    'PATH' => '/site_oo/crm/ml/index.php',
    'SORT' => 100,
  ),
  28 => 
  array (
    'CONDITION' => '#^/stssync/tasks/#',
    'RULE' => '',
    'ID' => 'bitrix:stssync.server',
    'PATH' => '/bitrix/services/stssync/tasks/index.php',
    'SORT' => 100,
  ),
  43 => 
  array (
    'CONDITION' => '#^/shop/settings/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.admin.page.controller',
    'PATH' => '/shop/settings/index.php',
    'SORT' => 100,
  ),
  31 => 
  array (
    'CONDITION' => '#^/about/gallery/#',
    'RULE' => '',
    'ID' => 'bitrix:photogallery',
    'PATH' => '/about/gallery/index.php',
    'SORT' => 100,
  ),
  35 => 
  array (
    'CONDITION' => '#^/services/idea/#',
    'RULE' => '',
    'ID' => 'bitrix:idea',
    'PATH' => '/services/idea/index.php',
    'SORT' => 100,
  ),
  110 => 
  array (
    'CONDITION' => '#^/marketing/ads/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/marketing/ads.php',
    'SORT' => 100,
  ),
  130 => 
  array (
    'CONDITION' => '#^/shop/terminal/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.terminal.payment.controller',
    'PATH' => '/terminal/index.php',
    'SORT' => 100,
  ),
  152 => 
  array (
    'CONDITION' => '#^/site_oo/sites/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.start',
    'PATH' => '/site_oo/sites/index.php',
    'SORT' => 100,
  ),
  248 => 
  array (
    'CONDITION' => '#^/calendar/open/#',
    'RULE' => '',
    'ID' => 'bitrix:calendar.open-events',
    'PATH' => '/calendar/open_events.php',
    'SORT' => 100,
  ),
  261 => 
  array (
    'CONDITION' => '#^/bi/statistics/#',
    'RULE' => '',
    'ID' => 'bitrix:biconnector.apachesuperset.workspace_analytic.controller',
    'PATH' => '/bi/statistics/index.php',
    'SORT' => 100,
  ),
  105 => 
  array (
    'CONDITION' => '#^/crm/activity/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.activity',
    'PATH' => '/crm/activity/index.php',
    'SORT' => 100,
  ),
  34 => 
  array (
    'CONDITION' => '#^/services/faq/#',
    'RULE' => '',
    'ID' => 'bitrix:support.faq',
    'PATH' => '/services/faq/index.php',
    'SORT' => 100,
  ),
  102 => 
  array (
    'CONDITION' => '#^/crm/tracking/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/crm/tracking/index.php',
    'SORT' => 100,
  ),
  14 => 
  array (
    'CONDITION' => '#^/mobile/webdav#',
    'RULE' => '',
    'ID' => 'bitrix:mobile.webdav.file.list',
    'PATH' => '/mobile/webdav/index.php',
    'SORT' => 100,
  ),
  115 => 
  array (
    'CONDITION' => '#^/marketing/rc/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/marketing/rc.php',
    'SORT' => 100,
  ),
  75 => 
  array (
    'CONDITION' => '#^/shop/catalog/#',
    'RULE' => '',
    'ID' => 'bitrix:catalog.productcard.controller',
    'PATH' => '//shop/catalog/index.php',
    'SORT' => 100,
  ),
  131 => 
  array (
    'CONDITION' => '#^/crm/terminal/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.terminal.payment.controller',
    'PATH' => '/terminal/index.php',
    'SORT' => 100,
  ),
  157 => 
  array (
    'CONDITION' => '#^/site_oo/onec/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.1c.start',
    'PATH' => '/site_oo/onec/index.php',
    'SORT' => 100,
  ),
  166 => 
  array (
    'CONDITION' => '#^/site_oo/mail/#',
    'RULE' => '',
    'ID' => 'bitrix:mail.client',
    'PATH' => '/site_oo/mail/index.php',
    'SORT' => 100,
  ),
  282 => 
  array (
    'CONDITION' => '#^/desktop/menu/#',
    'RULE' => '',
    'ID' => 'bitrix:intranet.menu',
    'PATH' => '/desktop_menu/index.php',
    'SORT' => 100,
  ),
  81 => 
  array (
    'CONDITION' => '#^/crm/invoice/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.invoice',
    'PATH' => '/crm/invoice/index.php',
    'SORT' => 100,
  ),
  22 => 
  array (
    'CONDITION' => '#^/marketplace/#',
    'RULE' => '',
    'ID' => 'bitrix:rest.marketplace',
    'PATH' => '/marketplace/index.php',
    'SORT' => 100,
  ),
  87 => 
  array (
    'CONDITION' => '#^/crm/catalog/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.catalog.controller',
    'PATH' => '/crm/catalog/index.php',
    'SORT' => 100,
  ),
  41 => 
  array (
    'CONDITION' => '#^/docs/manage/#',
    'RULE' => '',
    'ID' => 'bitrix:disk.common',
    'PATH' => '/docs/manage/index.php',
    'SORT' => 100,
  ),
  45 => 
  array (
    'CONDITION' => '#^/shop/stores/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.start',
    'PATH' => '/shop/stores/index.php',
    'SORT' => 100,
  ),
  99 => 
  array (
    'CONDITION' => '#^/crm/webform/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/crm/webform/index.php',
    'SORT' => 100,
  ),
  86 => 
  array (
    'CONDITION' => '#^/crm/product/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.product',
    'PATH' => '/crm/product/index.php',
    'SORT' => 100,
  ),
  46 => 
  array (
    'CONDITION' => '#^/shop/orders/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.order',
    'PATH' => '/shop/orders/index.php',
    'SORT' => 100,
  ),
  77 => 
  array (
    'CONDITION' => '#^/crm/contact/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.contact',
    'PATH' => '/crm/contact/index.php',
    'SORT' => 100,
  ),
  78 => 
  array (
    'CONDITION' => '#^/crm/company/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.company',
    'PATH' => '/crm/company/index.php',
    'SORT' => 100,
  ),
  180 => 
  array (
    'CONDITION' => '#^/site_oo/rpa/#',
    'RULE' => '',
    'ID' => 'bitrix:rpa.router',
    'PATH' => '/site_oo/rpa/index.php',
    'SORT' => 100,
  ),
  249 => 
  array (
    'CONDITION' => '#^/desktop/menu#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/desktop_menu/index.php',
    'SORT' => 100,
  ),
  250 => 
  array (
    'CONDITION' => '#^/dexika/corr/#',
    'RULE' => '',
    'ID' => 'dexika:app',
    'PATH' => '/dexika/corr/index.php',
    'SORT' => 100,
  ),
  134 => 
  array (
    'CONDITION' => '#^/workgroups/#',
    'RULE' => '',
    'ID' => 'bitrix:socialnetwork_group',
    'PATH' => '/workgroups/index.php',
    'SORT' => 100,
  ),
  40 => 
  array (
    'CONDITION' => '#^/docs/shared#',
    'RULE' => '',
    'ID' => 'bitrix:disk.common',
    'PATH' => '/docs/shared/index.php',
    'SORT' => 100,
  ),
  69 => 
  array (
    'CONDITION' => '#^/conference/#',
    'RULE' => '',
    'ID' => 'bitrix:im.conference.center',
    'PATH' => '/conference/index.php',
    'SORT' => 100,
  ),
  100 => 
  array (
    'CONDITION' => '#^/crm/button/#',
    'RULE' => '',
    'ID' => '',
    'PATH' => '/crm/button/index.php',
    'SORT' => 100,
  ),
  58 => 
  array (
    'CONDITION' => '#^/shop/buyer/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.order.buyer',
    'PATH' => '/shop/buyer/index.php',
    'SORT' => 100,
  ),
  179 => 
  array (
    'CONDITION' => '#^/site_oo/kb/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.start',
    'PATH' => '/site_oo/kb/index.php',
    'SORT' => 100,
  ),
  80 => 
  array (
    'CONDITION' => '#^/crm/quote/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.quote',
    'PATH' => '/crm/quote/index.php',
    'SORT' => 100,
  ),
  61 => 
  array (
    'CONDITION' => '#^/knowledge/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.pub',
    'PATH' => '/knowledge/index.php',
    'SORT' => 100,
  ),
  39 => 
  array (
    'CONDITION' => '#^/docs/sale/#',
    'RULE' => '',
    'ID' => 'bitrix:disk.common',
    'PATH' => '/docs/sale/index.php',
    'SORT' => 100,
  ),
  260 => 
  array (
    'CONDITION' => '#^/bi/source/#',
    'RULE' => '',
    'ID' => 'bitrix:biconnector.apachesuperset.workspace_analytic.controller',
    'PATH' => '/bi/source/index.php',
    'SORT' => 100,
  ),
  37 => 
  array (
    'CONDITION' => '#^/docs/pub/#',
    'RULE' => '',
    'ID' => 'bitrix:disk.external.link',
    'PATH' => '/docs/pub/extlinks.php',
    'SORT' => 100,
  ),
  79 => 
  array (
    'CONDITION' => '#^/crm/deal/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.deal',
    'PATH' => '/crm/deal/index.php',
    'SORT' => 100,
  ),
  68 => 
  array (
    'CONDITION' => '#^/crm/type/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.router',
    'PATH' => '/crm/type/index.php',
    'SORT' => 100,
  ),
  38 => 
  array (
    'CONDITION' => '#^//docs/all#',
    'RULE' => '',
    'ID' => 'bitrix:disk.aggregator',
    'PATH' => '/docs/index.php',
    'SORT' => 100,
  ),
  64 => 
  array (
    'CONDITION' => '#^/kb/group/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.start',
    'PATH' => '/kb/group/index.php',
    'SORT' => 100,
  ),
  76 => 
  array (
    'CONDITION' => '#^/crm/lead/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.lead',
    'PATH' => '/crm/lead/index.php',
    'SORT' => 100,
  ),
  126 => 
  array (
    'CONDITION' => '#^/pub/site/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.pub',
    'PATH' => '/pub/site/index.php',
    'SORT' => 100,
  ),
  129 => 
  array (
    'CONDITION' => '#^/terminal/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.terminal.payment.controller',
    'PATH' => '/terminal/index.php',
    'SORT' => 100,
  ),
  276 => 
  array (
    'CONDITION' => '#^vibe/edit/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.start',
    'PATH' => 'vibe/edit/index.php',
    'SORT' => 100,
  ),
  253 => 
  array (
    'CONDITION' => '#^/booking/#',
    'RULE' => '',
    'ID' => 'bitrix:booking',
    'PATH' => '/booking/index.php',
    'SORT' => 100,
  ),
  51 => 
  array (
    'CONDITION' => '#^/devops/#',
    'RULE' => '',
    'ID' => 'bitrix:rest.devops',
    'PATH' => '/devops/index.php',
    'SORT' => 100,
  ),
  128 => 
  array (
    'CONDITION' => '#^/market/#',
    'RULE' => '',
    'ID' => 'bitrix:market',
    'PATH' => '/market/index.php',
    'SORT' => 100,
  ),
  244 => 
  array (
    'CONDITION' => '#^/spaces/#',
    'RULE' => '',
    'ID' => 'bitrix:socialnetwork.spaces',
    'PATH' => '/spaces/index.php',
    'SORT' => 100,
  ),
  44 => 
  array (
    'CONDITION' => '#^/sites/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.start',
    'PATH' => '/sites/index.php',
    'SORT' => 100,
  ),
  21 => 
  array (
    'CONDITION' => '#^/rest/#',
    'RULE' => '',
    'ID' => NULL,
    'PATH' => '/bitrix/services/rest/index.php',
    'SORT' => 100,
  ),
  49 => 
  array (
    'CONDITION' => '#^/onec/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.1c.start',
    'PATH' => '/onec/index.php',
    'SORT' => 100,
  ),
  52 => 
  array (
    'CONDITION' => '#^/mail/#',
    'RULE' => '',
    'ID' => 'bitrix:mail.client',
    'PATH' => '/mail/index.php',
    'SORT' => 100,
  ),
  243 => 
  array (
    'CONDITION' => '#^/sign/#',
    'RULE' => '',
    'ID' => 'bitrix:sign.start',
    'PATH' => '/sign/index.php',
    'SORT' => 100,
  ),
  257 => 
  array (
    'CONDITION' => '#^/page/#',
    'RULE' => '',
    'ID' => 'bitrix:intranet.customsection',
    'PATH' => '/page/index.php',
    'SORT' => 100,
  ),
  66 => 
  array (
    'CONDITION' => '#^/rpa/#',
    'RULE' => '',
    'ID' => 'bitrix:rpa.router',
    'PATH' => '/rpa/index.php',
    'SORT' => 100,
  ),
  252 => 
  array (
    'CONDITION' => '#^/crm/#',
    'RULE' => '',
    'ID' => 'bitrix:crm.router',
    'PATH' => '/crm/index.php',
    'SORT' => 100,
  ),
  65 => 
  array (
    'CONDITION' => '#^/kb/#',
    'RULE' => '',
    'ID' => 'bitrix:landing.start',
    'PATH' => '/kb/index.php',
    'SORT' => 100,
  ),
  251 => 
  array (
    'CONDITION' => '#^/hr/#',
    'RULE' => '',
    'ID' => 'bitrix:humanresources.start',
    'PATH' => '/hr/index.php',
    'SORT' => 100,
  ),
);
