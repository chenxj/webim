<?php
/*
	[UCenter Home] (C) 2007-2008 Comsenz Inc.
	$Id: lang_cpmessage.php 12878 2009-07-24 05:59:38Z xupeng $
*/

if(!defined('IN_UCHOME')) {
	exit('Access Denied');
}

$_SGLOBAL['cplang'] = array(
	//common
	'do_success' => '进行的操作完成了',

	//admincp.php
	'enter_the_password_is_incorrect' => '输入的密码不正确，请重新尝试',
	'excessive_number_of_attempts_to_sign' => '您30分钟内尝试登录管理平台的次数超过了3次，为了数据安全，请稍候再试',

	//admincp.php

	//admin/admincp_ad.php
	'no_authority_management_operation' => '对不起,您没有权限进行本管理操作',
	'please_check_whether_the_option_complete_required' => '请检查必填选项是否填写完整',
	'please_choose_to_remove_advertisements' => '请至少选择一个要删除的广告',
	'no_authority_management_operation_edittpl' => '安全考虑，在线编辑模板功能默认关闭，并且只有创始人可以操作。如果您想使用此功能，请修改config.php中的相关配置。',
	'no_authority_management_operation_backup' => '安全考虑，数据库备份恢复操作只有创始人可以操作。如果您想使用此功能，请修改config.php中的相关配置。',

	//admin/admincp_album.php
	'at_least_one_option_to_delete_albums' => '请至少正确选择一个要删除的相册',

	//admin/admincp_backup.php
	'data_import_failed_the_file_does_not_exist' => '数据导入失败,文件不存在',
	'start_transferring_data' => '数据导入开始',
	'wrong_data_file_format_into_failure' => '数据导入失败,文件格式不对',
	'documents_were_incorrect_length' => '文件名长度不正确',
	'backup_table_wrong' => '备份表出错',
	'failure_writes_the_document_check_file_permissions' => '写入文件失败,请检查文件权限',
	'successful_data_compression_and_backup_server_to' => '数据成功备份并压缩至服务器',
	'backup_file_compression_failure' => '对不起,备份数据文件压缩失败,请检查目录权限',
	'shell_backup_failure' => 'SHELL备份失败',
	'data_file_does_not_exist' => '对不起, 数据文件不存在,请检查',
	'the_volumes_of_data_into_databases_success' => '分卷数据成功导入UCenter Home数据库.',
	'data_file_does_not_exist' => '对不起数据文件不存在请检查',
	'data_file_format_is_wrong_not_into' => '数据文件非格式，无法导入。',
	'directory_does_not_exist_or_can_not_be_accessed' => '目录不存在或无法访问，请检查 \\1 目录。',
	'vol_backup_database' => '分卷备份: 数据文件 # \\1 成功创建，程序将自动继续。',
	'complete_database_backup' => '恭喜您，全部 \\1 个备份文件成功创建，备份完成。',
	'decompress_data_files_success' => '数据文件 # \\1 成功解压缩，程序将自动继续。',
	'data_files_into_success' => '数据文件 # \\1 成功导入，程序将自动继续。',

	//admin/admincp_block.php
	'correctly_completed_module_name' => '请正确填写数据模块的名称',
	'a_call_to_delete_the_specified_modules_success' => '指定的数据调用模块删除成功了',
	'designated_data_transfer_module_does_not_exist' => '指定的数据调用模块不存在',
	'sql_statements_can_not_be_completed_for_normal' => '填写的SQL语句不能正常查询，请返回检查。<br>服务器反馈：<br>ERROR: \\1<br>ERRNO. \\2',
	'enter_the_next_step' => '进入下一步操作',
	'choose_to_delete_the_data_transfer_module' => '请至少选择一个要删除的数据调用模块',

	//admin/admincp_blog.php
	'the_correct_choice_to_delete_the_log' => '请至少正确选择一个要删除的日志',
	'the_correct_choice_to_add_topic' => '推荐到指定热点出错，请确认是否正确操作',
	'add_topic_success' => '推荐到热点完成了，产生了 \\1 个相关动态',

	//admin/admincp_cache.php

	//admin/admincp_censor.php

	//admin/admincp_comment.php
	'the_correct_choice_to_delete_comments' => '请至少正确选择一个要删除的评论',
	'choice_batch_action' => '请选择要进行的操作类型',

	//admin/admincp_config.php
	'ip_is_not_allowed_to_visit_the_area' => '当前的IP( \\1 )不在允许访问的IP范围内，请检查设置',
	'the_prohibition_of_the_visit_within_the_framework_of_ip' => '当前的IP( \\1 )在禁止访问的IP范围内，请检查设置',

	'config_uc_dir_error' => '设置的UCenter物理路径不正确，请返回检查',

	//admin/admincp_credit.php
	'rules_do_not_exist_points' => '该积分规则不存在',

	//admin/admincp_cron.php
	'designated_script_file_incorrect' => '指定的脚本文件不正确',
	'implementation_cycle_incorrect_script' => '设定的脚本执行周期不正确',

	//admin/admincp_item.php
	'choose_to_delete_events' => '请至少正确选择一个要删除的事件',

	//admin/admincp_mtag.php
	'choose_to_delete_the_columns_tag' => '请至少正确选择一个要删除的群组',
	'designated_to_merge_the_columns_do_not_exist' => '要合并到的新群组还没有创建，请先自行创建此群组后再进行合并',
	'the_successful_merger_of_the_designated_columns' => '合并指定的群组成功了',
	'columns_option_to_merge_the_tag' => '请至少正确选择一个要合并的群组',
	'lock_open_designated_columns_tag_success' => '锁定/开放指定的群组成功了',
	'recommend_designated_columns_tag_success' => '推荐/取消推荐指定的群组成功了',
	'choose_to_operate_columns_tag' => '请至少正确选择一个要操作的群组',

	'failed_to_change_the_length_of_columns' => '栏目长度变更失败，这可能是现存的数据已经超过新长度',

	//admin/admincp_pic.php
	'choose_to_delete_pictures' => '请至少正确选择一个要删除的图片',

	//admin/admincp_post.php
	'choose_to_delete_the_topic' => '请至少正确选择一个要删除的话题',

	//admin/admincp_profield.php
	'there_is_no_designated_users_columns' => '指定操作的群组栏目不存在',
	'choose_to_delete_the_columns' => '请正确选择要删除的栏目',
	'have_one_mtag' => '删除失败，请至少要保留一个群组栏目',
	
	//admin/admincp_poll.php
	'the_correct_choice_to_delete_the_poll' => '请至少正确选择一个要删除的投票',

	//admin/admincp_report.php
	'the_right_to_report_the_specified_id' => '请正确指定举报ID',

	//admin/admincp_share.php
	'please_delete_the_correct_choice_to_share' => '请正确选择要删除的分享',

	//admin/admincp_space.php
	'designated_users_do_not_exist' => '您指定的用户不存在',
	'choose_to_delete_the_space' => '请正确选择要删除的空间',
	'not_have_permission_to_operate_founder' => '你没有权限对创始人进行操作',
	'uc_error' => '与用户中心通信出错，请稍后再试',

	//admin/admincp_stat.php
	'choose_to_reconsider_statistical_data_types' => '请正确选择要重新统计的数据类型',
	'data_processing_please_wait_patiently' => '<a href="\\1">处理数据中( \\2 )，请耐心等候...</a> (<a href="\\3">强制终止</a>)',

	//admin/admincp_tag.php
	'choose_to_delete_the_tag' => '请至少正确选择一个要删除的标签',
	'to_merge_the_tag_name_of_the_length_discrepancies' => '指定的要合并到的tag名称字符长度不符合要求(1~30个字符)',
	'the_tag_choose_to_merge' => '请至少正确选择一个要合并的标签',
	'choose_to_operate_tag' => '请至少正确选择一个要操作的标签',

	//admin/admincp_template.php
	'designated_template_files_can_not_be_restored' => '指定的模板文件不能恢复',
	'template_files_editing_failure_check_directory_competence' => '指定的模板文件无法编辑,请检查 ./template 目录权限设置',

	//admin/admincp_thread.php
	'choosing_to_operate_the_topic' => '请正确选择要操作的话题',

	//admin/admincp_usergroup.php
	'user_group_does_not_exist' => '指定操作的用户组不存在',
	'user_group_were_not_empty' => '指定的用户组名不能为空',
	'integral_limit_duplication_with_other_user_group' => '指定的积分下限跟其他用户组重复',
	'system_user_group_could_not_be_deleted' => '系统用户组不能删除',
	'integral_limit_error' => '指定的积分下限上能超过999999999，下限不能低于-999999998',

	//admin/admincp_userapp.php
	'my_register_sucess' => '成功开启用户应用服务',
	'my_register_error' => '开启用户应用服务失败，失败原因：<br>\\2 (ERRCODE:\\1)<br><br><a href="http://www.discuz.net/index.php?gid=141" target="_blank">如果有疑问，请访问我们的技术论坛寻求帮助</a>。',
	'sitefeed_error' => '请正确添加动态标题、动态内容再提交发布',

	//admin/admincp_event.php
	'choose_to_delete_the_columns_event'=>'请选择要删除的活动',
	'choose_to_grade_the_columns_event'=>'请选择要设置的活动状态，新状态不能和原状态相同',
	'have_no_eventclass'=>'删除失败，请保留至少一个活动分类',
	'poster_only_jpg_allowed'=>'由于您的服务器不支持生成缩略图，您在此处只能上传 jpg 格式的图片'

);

?>