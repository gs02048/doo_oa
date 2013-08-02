<?php

/**
 * Description of MobiApi
 * NOTICE:1、发送系统消息和推送是要检测是否有接收人，各模块全部人的表示方式，0 or '' or other
 *        2、各模块已阅未阅人的处理方法
 *        3、各模块对删除操作的处理    
 *        4、updatetime检测更新的时候留意检测updatetime的条件,真删的时候找了附近一条记录修改其updatetime,如果检测条件过于精准，则检测不到这次删除操作，应放宽条件去检测
 */
class MobiApi {
    /*
     * 提交成功
     * @var int 
     */

    const SUCCESS_CODE = 1000;
    /*
     * 提交失败
     */
    const ERROR_CODE = 1001;
    /*
     * 参数错误
     */
    const ARG_ERROR_CODE = 1002;
    /*
     * 您没有权限进行此操作
     */
    const NO_PERMISSION_CODE = 1003;
    /*
     * 电话会议无配置信息
     */
    const NO_CONFIG_CODE = 1004;
    /*
     * 网络不通
     */
    const NO_NETWORK_CODE = 1005;
    /*
     * 网络连接成功
     */
    const CONNECT_SUCCESS_CODE = 1006;
    /*
     * 该记录不存在或已删除
     */
    const NO_RECODE_CODE = 1007;
    /*
     * 文件柜 创建文件夹失败
     */
    const CREATE_FAIL = 1008;
    /*
     * 文件柜 文件夹名字不能为空
     */
    const EMPTY_DIR = 1009;
    /*
     * 文件柜 该名称与系统文件夹重名，请改用其它名称
     */
    const SYS_RENAME = 1010;
    /*
     * 文件柜 无法[新建]，当前目录已设置成只读
     */
    const READ_ONLY = 1011;
    /*
     * 文件柜 你无权新建文件夹
     */
    const NO_LIMIT = 1012;
    /*
     * 文件柜 该文件夹名字已存在
     */
    const EXISTED = 1013;
    /*
     * 文件柜 该文件没有共享
     */
    const NOT_SHARE = 1014;
    /*
     * 手机注册 对方已撤销申请
     */
    const CANCLE_APPLY_CODE = 1015;
    /*
     * 手机注册  该消息已处理！
     */
    const PROCESSED_CODE = 1016;
    /*
     * 手机注册  该员工已加入贵公司，无需再次操作。
     */
    const JOINED_CODE = 1017;
    /*
     * 手机注册 创建员工失败！
     */
    const FAIL_ADDUSER_CODE = 1018;
    /*
     * 手机注册  更新员工失败!
     */
    const FAIL_UPDATEUSER_CODE = 1019;
    /*
     * 手机注册 添加授权码失败！
     */
    const LICENSE_ERROR_CODE = 1020;
    /*
     * 手机注册 拒绝申请失败！
     */
    const REFUSE_ERROR_CODE = 1021;
    /*
     * 网络传真 你没有传真账号
     */
    const NO_FAXNUMBER_CODE = 1022;

    private $returncode = array(
        '1000' => '提交成功',
        '1001' => '提交失败',
        '1002' => '参数错误',
        '1003' => '您没有权限进行此操作！',
        '1004' => '电话会议无配置信息',
        '1005' => '网络不通',
        '1006' => '网络连接成功',
        '1007' => '该记录不存在或已删除！',
        '1008' => '创建文件夹失败',
        '1009' => '文件夹名字不能为空',
        '1010' => '该名称与系统文件夹重名，请改用其它名称',
        '1011' => '无法[新建]，当前目录已设置成只读',
        '1012' => '你无权新建文件夹',
        '1013' => '该文件或文件夹名字已存在',
        '1014' => '该文件没有共享',
        '1015' => '对方已撤销申请！',
        '1016' => '该消息已处理！',
        '1017' => '该员工已加入贵公司，无需再次操作。',
        '1018' => '创建员工失败！',
        '1019' => '更新员工失败!',
        '1020' => '添加授权码失败！',
        '1021' => '拒绝申请失败！',
        '1022' => '你没有传真账号',
    );

    /*
     * 公告   MOBI端发布或者修改公告
     * @param $newsid 公告id,修改时有值，添加时不存在公告id
     * @param $content 公告内容
     * @param $title   公告标题
     * @param $receiptusers 接收公告的用户id
     * @param $newscategoryid  公告分类id
     * @param $readusers 已阅读用户列表
     * @param $istop  是否置顶
     * @param $cancomment 是否可评论
     * @param $dis        是否显示受众列表
     * @param $pubcom     是否公开评论
     * displayreceiptusers 是否显示受众人列表，1101显示，1015不显示	publicallreviews 是否公开所有评论，1101公开，1015不公开
     */
    public function edit_news($newsid, $userno, $enterpriseno, $content, $title, $receiptusers, $newscategoryid, $enddate, $readusers, $istop, $cancomment, $attachmentids,$dis,$pubcom) {
        Doo::loadClass('News');
        Doo::loadClass('SysMessage');
        Doo::loadClass("Exterprise");
        Doo::loadClass('Params');
        Doo::loadClass("Enum");

        $fields = array();
        $fields['userno'] = $userno;
        $fields['enterpriseno'] = $enterpriseno;
        $fields['receiptusers'] = $receiptusers;
        $fields['from'] = 1;
        $fields['content'] = $content;
        $fields['title'] = $title;
        $fields['newscategoryid'] = $newscategoryid;
        $fields['enddate'] = $enddate;
        $fields['readusers'] = $readusers;
        $fields['status'] = Enum::getStatusType('Normal');
        $fields['istop'] = $istop;
        $fields['cancomment'] = $cancomment;
        $fields['attachmentids'] = $attachmentids;
        $fields['displayreceiptusers'] = $dis;
        $fields['publicallreviews'] = $pubcom;

        if (empty($newsid)) {
            $feed_do_id = 20;
            $sys_do_id = 20;
            $fields['createtime'] = date(DATE_ISO8601);
            $fields['publishdate'] = date(DATE_ISO8601);
            $fields['updatetime'] = date(DATE_ISO8601);
            $insertNo = News::addNews($fields);
            $dotype = 10;
        } else {
            $feed_do_id = 21;
            $sys_do_id = 21;
            $fields['updatetime'] = date(DATE_ISO8601);
            $opt = array('where' => "newsid={$newsid}");
            $insertNo = $newsid;
            News::updateNews($fields, $opt);
            $dotype = 11;
        }
        if (!$insertNo) {
            $json = array('returncode' => self::ERROR_CODE);
            return $json;
        }
        try {
            //添加操作日志 添加或者修改公告
            News::addSysLog($insertNo, $dotype, $userno,$enterpriseno);
            //发新鲜事和系统消息
            $params = Params::getOneParams($enterpriseno, 1, $newscategoryid);
            $category = array();
            $category['category'] = $params['paramname'];
            $user = Exterprise::getEmployeeInfo($userno, $enterpriseno);
            Doo::loadClass('Feed');
            Feed::publish_feed($insertNo, 'newsid', $feed_do_id, $userno, $user->employeename, $enterpriseno, $receiptusers, '', '', $cancomment);
            $target_userids = empty($receiptusers) ? 0 : News::removeTheSpecifiedValueArray($readusers, $userno);
            SysMessage::send_sysMessage($insertNo, 'news', $sys_do_id, $userno, $user->employeename, $enterpriseno, '', $target_userids, $category);
        } catch (Exception $exc) {
            $exc->getLine();
        }
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 公告  mobi端获取公告列表
     * @param $search_type unread 未读 read 已读 空时为全部
     */

    public function get_news_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime, $search_type) {
        Doo::loadClass('Enum');
        Doo::loadClass('News');
        Doo::loadClass('Exterprise');
        $status = Enum::getStatusType('Normal');
        News::select_top_status($enterpriseno);
        $employee = Exterprise::getEmployeeInfo($userno, $enterpriseno);
        $e_createtime = $employee->createtime; //该员工加入公司的时间
        $single = 1;
        $opt = array();
        $opt_update = array();
        $x = $y = '';
        switch ($search_type) {
            case 'unread':
                $y = '!';
                break;
            case 'read':
                $x = '!';
                break;
            default :
                $single = 0;
                $where_str = 'publishdate>=? AND (FIND_IN_SET(?,receiptusers) OR (receiptusers="")) AND enterpriseno=?';
        }
        if (empty($single)) {
            $opt['param'] = array($e_createtime, $userno, $enterpriseno);
        } else {
            $where_str = 'publishdate>=? AND ((FIND_IN_SET(?,receiptusers) AND ' . $x . 'FIND_IN_SET(?,readusers)) OR (receiptusers="" AND ' . $y . 'FIND_IN_SET(?,readusers))) AND enterpriseno=?';
            $opt['param'] = array($e_createtime, $userno, $userno, $userno, $enterpriseno);
        }
        if ($lasttime != -1 && $pageindex == 1) {
            $opt_update['where'] = $where_str . ' AND updatetime>=?';
            $opt_update['param'] = $opt['param'];
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            array_push($opt_update['param'], $updatetime);
            $count = News::countNews($opt_update);
            if ($count == 0) {
                $json = array("returncode" => self::SUCCESS_CODE, "contents" => array());
                return $json;
            }
        }
        $opt['where'] = $where_str." AND status=$status";
        $count = News::countNews($opt);
        $opt['desc_1'] = 'istop';
        $opt['desc_2'] = 'publishdate';
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $result = News::getNews($opt);
        $list = array();
        foreach ($result as $value) {
            $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$value->userno} AND enterpriseno={$enterpriseno}"));
            $username = $userinfo->employeename;
            $list[] = array("newsid" => $value->newsid,
                "title" => $value->title,
                "publishdate" => $value->publishdate,
                "userno" => $value->userno,
                "username" => $username,
                "status" => $value->status,
                "istop" => $value->istop,
                "is_read" => News::is_read($userno, $value),
            );
        }
        $nowtime = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $nowtime, 'contents' => $list, 'deletes' => array());
        return $json;
    }
    /*
     * 公告  获取我发布的公告列表
     */
    public function get_mypublish_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime, $type){
        Doo::loadClass('Enum');
        Doo::loadClass('News');
        Doo::loadClass('Exterprise');
        $normal = Enum::getStatusType('Normal');
        $draft = Enum::getStatusType('Draft');
        $opt = array( 
            'where'=>'userno=? AND enterpriseno=?',
            );
        $nowtime = date(DATE_ISO8601);
        switch ($type){
            //我发布的
            case 'mypublish':
                $opt['where'] .= " AND publishdate<=? AND status=?";
                $param = array($userno,$enterpriseno,$nowtime,$normal);
                break;
            //定时发布
            case 'released':
                $opt['where'] .= ' AND publishdate>? AND status=?';
                $param = array($userno,$enterpriseno,$nowtime,$normal);
                break;
            //草稿
            case 'draft':
                $opt['where'] .= ' AND status=?';
                $param = array($userno,$enterpriseno,$draft);
                break;
        }
        if ($lasttime != -1 && $pageindex == 1) {
            $opt_update['where'] = $opt['where'] . ' AND updatetime>=?';
            $opt_update['param'] = $param;
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            array_push($opt_update['param'], $updatetime);
            $count = News::countNews($opt_update);
            if ($count == 0) {
                $json = array("returncode" => self::SUCCESS_CODE, "contents" => array());
                return $json;
            }
        }
        $opt['param'] = $param;
        $count = News::countNews($opt);
        $opt['desc_1'] = 'istop';
        $opt['desc_2'] = 'publishdate';
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $result = News::getNews($opt);
        $list = array();
        foreach ($result as $value) {
            $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$value->userno} AND enterpriseno={$enterpriseno}"));
            $username = $userinfo->employeename;
            $list[] = array(
                "newsid" => $value->newsid,
                "title" => $value->title,
                "publishdate" => $value->publishdate,
                "userno" => $value->userno,
                "username" => $username,
                "status" => $value->status,
                "istop" => $value->istop,
                "is_read" => News::is_read($userno, $value),
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 公告  获取具体公告详情 1、获取记录 2、设置已读 3、返回手机端需要字段
     * 公告接收人和已读人员:receiptusers，readusers
     * 如果受众是全部人员:receiptusers为空,readusers也为空，之后会把阅读人员逐个加到readusers中去,readusers最后为全部人的userno
     * 如果受众是部分人员:receiptusers,readusers为受众的userno,之后会把阅读人员逐个从readusers中清除，readusers最后为空
     * displayreceiptusers 是否显示受众人列表，1101显示，1015不显示	publicallreviews 是否公开所有评论，1101公开，1015不公开
     */

    public function get_news_detail($newsid, $userno, $enterpriseno) {
        Doo::loadClass('Comment');
        Doo::loadClass('Enum');
        Doo::loadClass('News');
        Doo::loadClass('Exterprise');
        Doo::loadClass('Permission');
        $status = Enum::getStatusType('Normal');
        $delete = Enum::getStatusType('Delete');
        $isadmin = Permission::checkUserPermission(ModuleCode::$News, ActionCode::$Admin,$userno,$enterpriseno);  //是否管理员
        $opt = array(
            'where' => 'newsid=? and status=? and enterpriseno=?',
            'param' => array($newsid, $status, $enterpriseno)
        );
        $value = News::getOneNews($opt);

        if ($value === false) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $flag = empty($value->receiptusers) ? true : false;
        $receid = "";
        News::setUserToRead($value->readusers, $newsid, $userno, $flag);
        if ($flag) {   //全部人
            $opt_user = array(
                'select' => 'userno',
                'where' => "status!=? AND (userstatus!=? OR userstatus is null) AND enterpriseno=?",
                'param' => array($delete, $delete, $enterpriseno)
            );
            $usernolist = Exterprise::getVwEnterpriseEmployeeUser($opt_user);
            foreach ($usernolist as $v) {
                if (!empty($v['userno'])) {
                    $receid .= "," . $v['userno'];
                }
            }
            $value->receiptusers = trim($receid, ',');
        } else {
            $readusers = array_filter(explode(',', $value->readusers));
            $receiptusers = array_filter(explode(',', $value->receiptusers));
            $value->readusers = implode(',', array_diff($receiptusers, $readusers));
        }
        $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$value->userno} AND enterpriseno={$enterpriseno}"));
        $username = $userinfo->employeename;
        $postmode = Enum::getCommentMode('News');
        $opt['where'] = 'postmode=? AND status=? AND postid=?';
        if($isadmin || $value->userno == $userno || $value->publicallreviews == 1101){  //是否可看到全部评论
            $canview = true;
            $opt['param'] = array($postmode, $status, $newsid);
        }else{
            $user = Exterprise::getUserEmployeeInfo(array('where' => "userno={$userno} AND enterpriseno={$enterpriseno}"));
            $canview = false;
            $opt['where'] .= 'AND (userno=? OR content LIKE ?)';
            $opt['param'] = array($postmode,$status,$newsid,$userno,"%@$user->employeename%");
        }
        $first_comment = Comment::getOneComment($opt);
        $comment_count = Comment::coutComments($opt);
        if ($first_comment != null) {
            $first_comment = $first_comment['username'] . ":" . $first_comment['content'];
        }
        $tousers = explode(',', $value->receiptusers);
        $prior_users = implode(',', array_slice($tousers, 0, 3));
        
        $content = array("newsid" => $value->newsid,
            "title" => $value->title,
            "content" => $value->content,
            "publishdate" => $value->publishdate,
            "userno" => $value->userno,
            "username" => $username,
            'prior_users' => $prior_users,
            'firstcomment' => $first_comment,
            'commentcount' => $comment_count,
            "readusers" => $value->readusers,
            "istop" => $value->istop,
            'cancomment' => $value->cancomment,
            "attachmentids" => $value->attachmentids,
            "receiptusers" => $value->receiptusers,
            'displayreceiptusers'=>$value->displayreceiptusers,     //是否公开受众
            'publicallreviews'=>$value->publicallreviews,           //是否公开评论
            'canview'=>$canview
                );
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $content);
        return $json;
    }

    /*
     * 公告  删除公告详情
     * 是公告的管理员并有发布公告的权限才能删除公告，否则无权限
     */

    public function delete_news($newsid, $userno, $enterpriseno) {
        Doo::loadClass('Permission');
        Doo::loadClass('Enum');
        Doo::loadClass('News');
        $delete = Enum::getStatusType('Delete');
        $isadmin = Permission::checkUserPermission(ModuleCode::$News, ActionCode::$Admin,$userno,$enterpriseno); //管理员
        $publish = Permission::checkUserPermission(ModuleCode::$News, ActionCode::$Publish,$userno,$enterpriseno); //发布
        if (!$isadmin && !$publish) {
            $json = array('returncode' => self::NO_PERMISSION_CODE);
        } else {
            $fields = array('status' => $delete, 'updatetime' => date(DATE_ISO8601));  
            $opt = array(
                'where' => 'newsid=? AND enterpriseno=?',
                'param' => array($newsid, $enterpriseno)
            );
            News::updateNews($fields, $opt);
            //添加系统日志
            News::addSysLog($newsid, 12, $userno, $enterpriseno);
            $json = array('returncode' => self::SUCCESS_CODE);
        }
        return $json;
    }

    /*
     * mobi端添加或修改汇报
     * @param $reportid 若reportid有值，则为修改汇报，否则是添加汇报
     * @param $title 汇报标题
     * @param $content 汇报内容
     * @param $type 分类(1=日报，2=周报，3=月报，4=季报,5=年报，6=其它报)
     * @param $starttime 开始时间
     * @param $endtime 结束时间
     * @param $tousers 接收汇报人
     * @param $cctousers 抄送人
     * @param $attachmentid 附件id
     * @return self::returncode 成功、失败
     */

    public function edit_report($reportid, $userno, $enterpriseno, $title, $content, $type, $starttime, $endtime, $tousers, $cctousers, $attachmentid,$item) {
        Doo::loadClass('Exterprise');
        Doo::loadClass("Enum");
        Doo::loadModel('EnterpriseReports');
        Doo::loadClass('Feed');
        Doo::loadClass('SysMessage');
        Doo::loadClass('Common');
        Doo::loadClass('Syslog');
        Doo::loadClass('Plan');

        $report = new EnterpriseReports();
        $report->title = $title;
        $report->content = $content;
        $report->userno = $report->updateuser = $userno;
        $report->enterpriseno = $enterpriseno;
        $report->tousers = $tousers;
        $report->type = $type;
        $report->createtime = $report->updatetime = date('Y-m-d H:i:s');
        $report->starttime = $starttime;
        $report->endtime = $endtime;
        $report->cctousers = $cctousers;
        $report->viewusers = '';
        $report->viewpermission = 1;
        $report->status = Enum::getStatusType('Normal');
        $report->attachments = $attachmentid;
        $report->from = 1;
        $report->plantoid = 0;

        if (empty($reportid)) {
            $feed_do_id = 30;
            $sys_do_id = 17;
            $reportlog = 19;
            $reportid = $report->insert();
            $report->reportid = $reportid;
        } else {
            $reportlog = 20;
            $feed_do_id = 31;
            $sys_do_id = 18;
            $report->createtime = $report->viewusers = $report->cctousers = null;
            $report->reportid = $reportid;
            $report->update();
        }
        if ($reportid <= 0) {
            $json = array('returncode' => self::ERROR_CODE);
            return $json;
        }else{
            if($item){
                $item = json_decode(stripslashes($item),TRUE);
                $report->item_data = $item;
                Plan::addItem($report,'EnterpriseReportitems');
            }
        }
        try {
            Syslog::send_syslog($reportid, 'report', $reportlog, $userno, '', $enterpriseno, Common::getIP());
            //新鲜事 系统消息
            $userinfo = Exterprise::getUserInfo(array('where' => "userno={$userno}"));
            Feed::publish_feed($reportid, 'reportid', $feed_do_id, $userno, '来自手机', $enterpriseno, $tousers);
            $reusers = empty($cctousers) ? $tousers : $tousers . ',' . $cctousers;
            SysMessage::send_sysMessage($reportid, 'report', $sys_do_id, $userno, $userinfo->username, $enterpriseno, '', $reusers);
        } catch (Exception $exc) {
            $exc->getTraceAsString();
        }
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 获取汇报详情
     * @param $reprotid 汇报id
     * @param $enterpriseno 公司id
     * @return $content 汇报详情
     */

    public function get_report_detail($reportid, $enterpriseno) {
        Doo::loadClass("Enum");
        Doo::loadClass("Comment");
        Doo::loadClass('Report');
        Doo::loadClass('Plan');
        Doo::loadClass('Exterprise');
        $delete = Enum::getStatusType('Delete');
        $check = array(
            'where' => 'enterpriseno=? AND status!=?',
            'param' => array($enterpriseno, $delete)
        );
        $info = Report::get_report_info($reportid, $check);
        if ($info === false) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $value = Plan::import_data($reportid, null, 'EnterpriseReports', 'EnterpriseReportitems');
        $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$value->userno} AND enterpriseno={$enterpriseno}"));
        $username = $userinfo->employeename;
        $postmode = Enum::getCommentMode('Report');
        $status = Enum::getStatusType('Normal');
        $opt['where'] = 'postmode=? AND status=? AND postid=?';
        $opt['param'] = array($postmode, $status, $reportid);
        $first_comment = Comment::getOneComment($opt);
        $comment_count = Comment::coutComments($opt);
        if ($first_comment != null) {
            $first_comment = $first_comment['username'] . ":" . $first_comment['content'];
        }
        $tousers = explode(',', $value->tousers);
        $prior_users = implode(',', array_slice($tousers, 0, 3));
        $item = $value->item_data;
        $content = array(
            "reportid" => $value->reportid,
            "title" => $value->title,
            "content" => $value->content,
            "starttime" => $value->starttime,
            "endtime" => $value->endtime,
            "userno" => $value->userno,
            "username" => $username,
            'cctousers' => $value->cctousers,
            'ccreadusers' => $value->ccreadusers,
            "readusers" => $value->readusers,
            "tousers" => $value->tousers,
            'prior_users' => $prior_users,
            'firstcomment' => $first_comment,
            'commentcount' => $comment_count,
            "attachments" => $value->attachments,
            "createtime" => $value->createtime,
            'reportitem' => $item
        );
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $content);
        return $json;
    }

    /*
     * 汇报  汇报列表
     */

    public function get_report_list($userno, $enterpriseno, $pageindex, $pagesize, $updatetime) {
        Doo::loadClass("Exterprise");
        Doo::loadClass("Enum");
        Doo::loadModel('EnterpriseReports');
        Doo::loadClass("Report");
        $normal = Enum::getStatusType('Normal');
        $report = new EnterpriseReports();
        $opt = array(
            'where' => 'enterpriseno=? AND userno=? AND status=?',
            'param' => array($enterpriseno, $userno, $normal),
            'desc' => 'createtime'
        );
        $nowtime = date('Y-m-d H:i:s');
        if ($updatetime != -1 && $pageindex == 1) {
            $lastupdatetime = date('Y-m-d H:i:s', $updatetime);
            $tem_opt = array(
                'where' => 'enterpriseno=? AND userno=? AND updatetime between ? AND ?',
                'param' => array($enterpriseno, $userno, $lastupdatetime, $nowtime)
            );
            $count = $report->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = $report->count($opt);
        $opt['limit'] = ($pageindex - 1) * $pagesize . ',' . $pagesize;
        $result = $report->find($opt);
        $list = array();
        foreach ($result as $value) {
            $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$value->userno} AND enterpriseno={$enterpriseno}"));
            $username = $userinfo->employeename;
            $readstatus = Report::get_unread_amount($value);
            $list[] = array(
                "reportid" => $value->reportid,
                "title" => $value->title,
                "content" => $value->content,
                "type" => $value->type,
                "starttime" => $value->starttime,
                "endtime" => $value->endtime,
                "userno" => $value->userno,
                "username" => $username,
                "status" => $value->status,
                "createtime" => $value->createtime,
                "readstatus" => $readstatus,
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 汇报  批阅汇报列表
     */

    public function get_todo_report_list($userno, $enterpriseno, $pageindex, $pagesize, $updatetime) {
        Doo::loadClass("Exterprise");
        Doo::loadClass("Enum");
        Doo::loadModel('EnterpriseReports');
        $normal = Enum::getStatusType('Normal');
        $report = new EnterpriseReports();
        $opt = array(
            'where' => 'enterpriseno=? AND FIND_IN_SET(?,tousers) AND status=?',
            'param' => array($enterpriseno, $userno, $normal)
        );
        $nowtime = date('Y-m-d H:i:s');
        if ($updatetime != -1 && $pageindex == 1) {
            $lasttime = date('Y-m-d H:i:s', $updatetime);
            $tem_opt = array(
                'where' => 'enterpriseno=? AND FIND_IN_SET(?,tousers) AND updatetime BETWEEN ? AND ?',
                'param' => array($enterpriseno, $userno, $lasttime, $nowtime)
            );
            $count = $report->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = $report->count($opt);
        $page = ($pageindex - 1) * $pagesize;
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $opt['desc'] = 'createtime';
        $result = $report->find($opt);
        $list = array();
        foreach ($result as $value) {
            $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$value->userno} AND enterpriseno={$enterpriseno}"));
            $username = $userinfo->employeename;
            $list[] = array(
                "reportid" => $value->reportid,
                "title" => $value->title,
                "content" => $value->content,
                "type" => $value->type,
                "starttime" => $value->starttime,
                "endtime" => $value->endtime,
                "userno" => $value->userno,
                "username" => $username,
                "status" => $value->status,
                "createtime" => $value->createtime,
                "readstatus" => strstr($value->readusers, "$userno") ? 1 : 0
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 汇报  批阅汇报
     */

    public function read_report($reportid, $userno, $enterpriseno) {
        Doo::loadModel('EnterpriseReports');
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        $report = new EnterpriseReports();
        $opt = array(
            'where' => 'reportid=? AND enterpriseno=? AND status=?',
            'param' => array($reportid, $enterpriseno, $normal)
        );
        $reportInfo = $report->getOne($opt);

        if ($reportInfo === false) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }

        $readusers = $reportInfo->readusers;
        $report->reportid = $reportid;           //已阅读用户userno列表，多个用,分开
        if ($readusers == '') {
            $report->readusers = $userno;
            $report->updatetime = date('Y-m-d H:i:s"');
            $report->update();
        } elseif (strstr("$userno", $readusers) === false) {
            $report->readusers = empty($readusers) ? $userno : $readusers . ',' . $userno;
            $report->updatetime = date('Y-m-d H:i:s"');
            $report->update();
        }
        $cctousers = $reportInfo->cctousers;
        $ccreadusers = $reportInfo->ccreadusers;
        $pos = strpos($cctousers, $userno);
        if ($pos !== false) {
            if ($ccreadusers == '') {
                $report->ccreadusers = $userno;
                $report->update();
            } elseif (strstr("$userno", $ccreadusers) === false) {
                $report->ccreadusers = $ccreadusers . ',' . $userno;
                $report->update();
            }
        }
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 获取全部汇报
     */

    public function get_all_report_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass("Exterprise");
        Doo::loadClass("Enum");
        Doo::loadClass("Permission");
        Doo::loadModel('EnterpriseReports');
        $report = new EnterpriseReports();
        $normal = Enum::getStatusType('Normal');
        $admin = Permission::checkUserPermission("Report", "Admin",$userno,$enterpriseno); //管理
        $opt = array(
            'where'=>'(1=? or userno=? or viewpermission=0 or find_in_set(?,cctousers) or (viewpermission=1 and find_in_set(?,tousers)) or (viewpermission=2 and find_in_set(?,viewusers))) and enterpriseno=?',
            'param'=>array($admin,$userno, $userno, $userno, $userno,$enterpriseno),
            'desc'=>'createtime'
        );
        $nowtime = date('Y-m-d H:i:s');

        if ($lasttime != -1 && $pageindex == 1) {
            $updatetime = date("Y-m-d H:i:s", $lasttime);
            $param = $opt['param'];
            array_push($param, $updatetime, $nowtime);
            $count_opt = array(
                'where' => $opt['where'] . ' AND updatetime BETWEEN ? AND ?',
                'param' => $param
            );
            $count = $report->count($count_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $opt['where'] .= " AND status={$normal}";
        $count = $report->count($opt);
        $page = ($pageindex - 1) * $pagesize;
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $result = $report->find($opt);

        $list = array();
        foreach ($result as $value) {
            $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$value->userno} AND enterpriseno={$enterpriseno}"));
            $username = $userinfo->employeename;
            $list[] = array(
                "reportid" => $value->reportid,
                "title" => $value->title,
                "content" => $value->content,
                "type" => $value->type,
                "starttime" => $value->starttime,
                "endtime" => $value->endtime,
                "userno" => $value->userno,
                "username" => $username,
                "status" => $value->status,
                "createtime" => $value->createtime
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 会议  添加或者修改会议
     * @param $meetingid 有id则为修改会议，无id为添加会议
     * @param $hostid
     */

    public function edit_meeting($meetingid, $userno, $enterpriseno, $hostid, $meetingname, $meetingcontent, $meetingdate, $starttime, $endtime, $meetingaddress, $joiners, $attachmentid, $viewusers) {
        Doo::loadClass("Meeting");
        Doo::loadClass("Exterprise");
        Doo::loadClass("SysMessage");
        Doo::loadClass('Today');
        Doo::loadClass('Common');
        Doo::loadClass('Syslog');
        Doo::loadModel("EnterpriseMeeting");
        $user = Exterprise::getUserInfo(array('where' => 'userno=?', 'param' => array($hostid)));
        $curr_user = Exterprise::getUserInfo(array('where' => 'userno=?', 'param' => array($userno)));
        $presenter = $user->username;
        $meeting = new EnterpriseMeeting();
        $meeting->userno = $userno;
        $meeting->createtime = $meeting->lastupdatetime = date('Y-m-d H:i:s');
        $meeting->enterpriseno = $enterpriseno;
        $meeting->subject = $meetingname;
        $meeting->content = $meetingcontent;
        $meeting->presenteruserno = $hostid;
        $meeting->presenter = $presenter;
        $meeting->starttime = $meetingdate . " " . $starttime;
        $meeting->endtime = $meetingdate . " " . $endtime;
        $meeting->meetingroomname = $meetingaddress;
        $meeting->joinusers = $joiners;
        $meeting->attachmentids = $attachmentid;
        $meeting->from = 1;
        $meeting->status = 1101;
        $meeting->viewmeetingusers = $viewusers == 1 ? $meeting->joinusers : 0;
        if (empty($meetingid)) {
            $logid = 4;
            $sys_do_id = 40;
            $meeting->acceptusers = $hostid;
            $meetingid = $meeting->insert();
            $meeting = Meeting::get_meeting_byid($meetingid);
            Today::add_meeting($meeting);
        } else {
            $logid = 5;
            $meeting->meetingid = $meetingid;
            $sys_do_id = 46;
            $meeting->update();
        }
        if (!$meetingid) {
            $json = array('returncode' => self::ERROR_CODE);
            return $json;
        }
        try {
            Syslog::send_syslog($meetingid, "meeting", $logid, $userno, '', $enterpriseno, Common::getIP());
            SysMessage::send_sysMessage($meetingid, "meeting", $sys_do_id, $userno, $curr_user->username, $enterpriseno, '', $meeting->joinusers);
        } catch (Exception $exc) {
            $exc->getTraceAsString();
        }
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 获取会议列表
     */

    public function get_meeting_list($userno, $enterpriseno, $pageindex, $pagesize, $type, $status, $lasttime) {
        Doo::loadClass("Meeting");
        Doo::loadModel("EnterpriseMeeting");
        Doo::loadClass("User");
        Doo::loadClass("Common");
        $nowtime = date('Y-m-d H:i:s');
        $opt = array();
        $opt['where'] = 'enterpriseno=? AND subject not like ? AND status!=? ';
        if ($type == 'all') {
            $opt['where'] .= ' AND (FIND_IN_SET(?,viewmeetingusers) OR FIND_IN_SET(?,joinusers) OR joinusers=? OR viewmeetingusers=? OR userno=? OR presenteruserno=?)';
            $opt['param'] = array($enterpriseno, '%电话会议%', '1001', $userno, $userno, '-1', 0, $userno, $userno);
        } else {
            $opt['where'] .= ' AND (FIND_IN_SET(?,joinusers)  OR joinusers=? OR userno=? OR presenteruserno=? )';
            $opt['param'] = array($enterpriseno, '%电话会议%', '1001', $userno, '-1', $userno, $userno);
        }
        switch ($status) {
            case '':
                break;
            case 'start':
                $opt['where'] .= ' AND starttime>?';
                array_push($opt['param'], $nowtime);
                break;
            case 'starting':
                $opt['where'] .= ' AND starttime<=? AND endtime>?';
                array_push($opt['param'], $nowtime, $nowtime);
                break;
            case 'started':
                $opt['where'] .= ' AND endtime<=?';
                array_push($opt['param'], $nowtime);
                break;
        }
        $meeting = new EnterpriseMeeting();
        if ($lasttime != -1 && $pageindex == 1) {
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $tem_opt = array(
                'where' => 'enterpriseno=? AND lastupdatetime BETWEEN ? AND ?',
                'param' => array($enterpriseno, $updatetime, $nowtime)
            );
            $count = $meeting->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'data' => array());
                return $json;
            }
        }
        $count = $meeting->count($opt);
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $opt['desc'] = 'starttime';
        $result = Meeting::findMeetingList($opt);

        $list = array();
        foreach ($result as $value) {
            $joinusers = array();
            $avatars = array();
            if (!empty($value->joinusers)) {
                $arr = trim($value->joinusers,',');
                $users = User::find_user_list(array("where" => "userno IN ($arr)"));
                if ($users != null) {
                    foreach ($users as $val) {
                        $joinusers[] = $val->username;
                        $avatar = Common::getImageThumb($val->userno, 1);
                        $avatars[] = Doo::conf()->DOCS_URL . $avatar;
                    }
                }
            }
            $status = 0;
            if (Common::date_contrast($value->starttime) > 0) {
                $status = 0;  //未开始
            } else if (Common::date_contrast($value->starttime) < 0 && Common::date_contrast($value->endtime) > 0) {
                $status = 1;  //进行中
            } else {
                $status = 2; //已结束
            }
            $list[] = array(
                "meetingid" => $value->meetingid,
                "meetingname" => $value->subject,
                "meetingdate" => $value->starttime,
                "starttime" => $value->starttime,
                "endtime" => $value->endtime,
                "meetingaddress" => $value->meetingroomname,
                "meetingcontent" => $value->content,
                "hostname" => $value->presenter,
                "joiners" => implode(",", $joinusers),
                "status" => $status,
                "remark" => $value->remark,
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 获取会议详情
     * 字段 joinusers 参与人员 如果是全部人是为-1
     */

    public function get_meeting_detail($enterpriseno, $meetingid) {
        Doo::loadClass("Common");
        Doo::loadClass("Project");
        Doo::loadClass('Enum');
        Doo::loadClass('Meeting');
        $value = Meeting::get_meeting_byid($meetingid, $enterpriseno);
        if ($value === FALSE) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $status = 0;
        if (Common::date_contrast($value->starttime) > 0) {
            $status = 0;  //未开始
        } else if (Common::date_contrast($value->starttime) < 0 && Common::date_contrast($value->endtime) > 0) {
            $status = 1;  //进行中
        } else {
            $status = 2; //已结束
        }
        $arruser = $this->set_userinfo_meetingjoin($value->enterpriseno, $value->joinusers, $value->presenteruserno, $value->acceptusers, $value->rejectusers, $value->rejectreason);
        $content = array(
            "meetingid" => $value->meetingid,
            "meetingname" => $value->subject,
            "meetingdate" => $value->starttime,
            "starttime" => $value->starttime,
            "endtime" => $value->endtime,
            "meetingaddress" => $value->meetingroomname,
            "meetingcontent" => $value->content,
            "hostname" => $value->presenter, //implode(",", $avatars),
            "joinusers" => $arruser,
            'joinuser' => $value->joinusers,
            'userno' => $value->userno,
            "attachments" => $value->attachmentids,
            "status" => $status,
            "remark" => $value->remark
        );
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $content);
        return $json;
    }

    /*
     * 返回各个参会人员的情况
     */

    private function set_userinfo_meetingjoin($enterpriseno, $joinusers, $presenteruserno, $acceptusers, $rejectusers, $rejectreason) {
        Doo::loadClass("Enum");
        $delete = Enum::getStatusType("Delete");
        $users = array();
        Doo::loadClass('Exterprise');
        $opt = array(
            'where' => 'status!=? AND userno>? AND length(username)>? AND isleave=? AND enterpriseno=?',
            'param' => array($delete, 0, 0, 0, $enterpriseno)
        );
        if (empty($joinusers) || $joinusers == -1) {
            
        } else {
            $opt['where'] .= ' AND userno IN (' . $joinusers . ')';
        }
        $res = Exterprise::getVwEnterpriseEmployeeUser($opt);
        foreach ($res as $v) {
            $value = $v['userno'];
            if ($value == '')
                continue;
            $reg = '/(^|(.)*\D)' . $value . '($|\D(.)*)/';
            $title = '';
            $mark = '';
            if ($presenteruserno == $value) {
                $title = '主持人';
                $mark = '0';
            } else if (preg_match($reg, $acceptusers)) {
                $title = '已接受';
                $mark = '1';
            } else if (preg_match($reg, $rejectusers)) {
                $title = '已拒绝';
                $mark = '2';
                $strlist = explode('|||', $rejectreason);
                foreach ($strlist as $value1) {
                    $temp = explode('$$$', $value1);
                    if ($temp[0] == $value) {
                        $title = '已拒绝:' . $temp[1];
                    }
                }
            } else {
                $mark = '3';
                $title = '未处理';
            }
            $users[] = array('title' => $title, 'mark' => $mark, 'userno' => $value, 'username' => $v['employeename']);
        }
        return $users;
    }

    /*
     * 确认加入会议
     */

    public function accept_meeting($userno, $meetingid) {
        Doo::loadClass("Meeting");
        $result = Meeting::joinMeeting($userno, $meetingid);
        if ($result == -1) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        } else {
            $json = array('returncode' => self::SUCCESS_CODE);
            return $json;
        }
    }

    /*
     * 拒绝参加会议
     */

    public function reject_meeting($userno, $meetingid, $reason) {
        Doo::loadClass("Meeting");
        $result = Meeting::rejectMeeting($userno, $meetingid, $reason);
        if ($result == -1) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        } else {
            $json = array('returncode' => self::SUCCESS_CODE);
            return $json;
        }
    }

    /*
     * 获取会议的任务列表
     */

    public function find_task_by_meetingid($userno, $enterpriseno, $meetingid) {
        Doo::loadClass("User");
        Doo::loadClass("Meeting");
        $result = Meeting::getMeetingTasks($userno, $enterpriseno, $meetingid);
        $status = array('1101' => 1, '1102' => 2, '1104' => 3, '1201' => 4, '1106' => 5, '1021' => 6);
        $list = array();
        foreach ($result as $value) {
            $joinusers = array();
            if (!empty($value->joinusers)) {
                $subone = explode(',', $value->joinusers);
                $subtwo = array_filter($subone);
                $arr = implode(',', $subtwo);

                $userlist = User::find_user_list(array("where" => "userno IN ($arr)"));
                if ($userlist != null) {
                    foreach ($userlist as $val) {
                        $joinusers[] = $val->username;
                    }
                }
            }
            $list[] = array(
                "projectid" => $value->projectid,
                "taskid" => $value->taskid,
                "taskname" => $value->taskname,
                "startdate" => $value->startdate,
                "finishdate" => $value->finishdate,
                "percentcomplete" => $value->percentcomplete,
                "notes" => $value->notes,
                "avatars" => '',
                "joinusers" => implode(",", $joinusers),
                "accepter" => $value->accepter,
                "creator" => $value->creator,
                "count" => count($result),
                "createtime" => $value->createtime,
                "status" => @$status["$value->status"]
            );
        }
        $json = array('returncode' => self::SUCCESS_CODE, 'contents' => $list);
        return $json;
    }

    /*
     * 电话会议列表
     */

    public function get_phone_meeting_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass("Meeting");
        Doo::loadModel("EnterpriseMeeting");
        Doo::loadModel("EnterpriseMeetingConference");
        Doo::loadClass("User");
        Doo::loadClass("Common");
        //mysql关键字from  慎用
        $opt = array(
            'where' => '`from`=? AND cantelconference=? AND enterpriseno=? AND (FIND_IN_SET(?,joinusers) OR userno=? OR presenteruserno=?)',
            'param' => array(1, 1, $enterpriseno, $userno, $userno, $userno)
        );
        $meeting = new EnterpriseMeeting();
        $nowtime = date('Y-m-d H:i:s');
        if ($lasttime != -1 && $pageindex == 1) {
            $updatetime = date("Y-m-d H:i:s", $lasttime);
            $param = $opt['param'];
            array_push($param, $updatetime, $nowtime);
            $tem_opt = array(
                'where' => $opt['where'] . ' AND createtime BETWEEN ? AND ?',
                'param' => $param
            );
            $count = $meeting->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = $meeting->count($opt);
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $opt['desc'] = 'starttime';

        $result = Meeting::findMeetingList($opt);
        $list = array();
        foreach ($result as $key => $value) {
            $m = new EnterpriseMeetingConference();
            $m->meetingid = $value->meetingid;
            $conference = $m->getOne();
            if (empty($conference)) {
                unset($result[$key]);
                continue;
            }
            $status = 0;
            if (Common::date_contrast($value->starttime) > 0) {
                $status = 0;  //未开始
            } else if (Common::date_contrast($value->starttime) < 0 && Common::date_contrast($value->endtime) > 0) {
                $status = 1;  //进行中
            } else {
                $status = 2; //已结束
            }
            $list[] = array(
                "mid" => $value->meetingid,
                "title" => $value->subject,
                "starttime" => $value->starttime,
                'state' => $status,
                'hostname' => $value->presenteruserno,
                "createtime" => $value->createtime,
                'teleconferenceid' => isset($conference->conferenceid) ? $conference->conferenceid : ''
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 电话会议添加
     */

    public function edit_phone_meeting($userno, $enterpriseno, $joinuser, $phones, $title) {
        Doo::loadModel("EnterpriseMeeting");
        Doo::loadClass("Meeting");
        $meeting = new EnterpriseMeeting();
        $meeting->userno = $userno;
        $meeting->enterpriseno = $enterpriseno;
        $meeting->subject = $title;
        $meeting->presenteruserno = $userno;
        $meeting->presenter = $this->get_user_info($userno, 'username', $enterpriseno, true);
        $meeting->createtime = date('Y-m-d H:i:s', time());
        $meeting->meetingroomname = '其他';
        $meeting->from = 1;
        $meeting->cantelconference = 1;
        $meeting->joinusers = $joinuser;
        $meeting->starttime = date('Y-m-d H:i:s', time());
        $meeting->endtime = date('Y-m-d H:i:s', time());

        $account = "";
        $opt['where'] = 'enterpriseno = ?';
        $opt['param'] = array($enterpriseno);
        $opt['limit'] = 1;
        $config = Meeting::find_conference_config($opt);
        if ($config != null) {
            $account = $config->openedphone;
        }
        if ($account == '') {
            $json = array('returncode' => self::NO_CONFIG_CODE);
            return $json;
        }
        Doo::loadClass("OAClient");
        $subres = OAClient::create_conference($enterpriseno, $userno, $account, $phones);
        if ($subres == false) {
            $json = array('returncode' => self::NO_NETWORK_CODE);
            return $json;
        }
        $res = json_decode($subres);
        if (!empty($res->error) || empty($res->data)) {
            $json = array('returncode' => self::NO_NETWORK_CODE);
            return $json;
        }
        $conferenceid = $res->data;
        if ($conferenceid) {
            //更新状态
            Doo::loadClass("Meeting");
            $arrPhones = explode(',', $phones);
            $arrUserno = explode(',', $joinuser);
            $mid = $meeting->insert();
            if (!$mid) {
                $json = array('returncode' => self::ERROR_CODE);
                return $json;
            }
            //添加电话会议
            Meeting::add_meeting_conference($mid, $conferenceid, $userno, $enterpriseno);
            //更新状态
            $i = 0;
            foreach ($arrPhones as $telphone) {
                if ($arrUserno[$i] != 0)
                    $username = $this->get_user_info($arrUserno[$i], 'username', $enterpriseno, $first = true);
                else {
                    $username = '新建人员';
                }
                Meeting::update_meeting_phone_state($mid, $conferenceid, $telphone, date('Y-m-d H:i:s'), 'Initial', 0, $enterpriseno, $arrUserno[$i], $username);
                $i++;
            }
        }
        $json = array('returncode' =>self::SUCCESS_CODE, 'content' => array('message' => self::CONNECT_SUCCESS_CODE, 'mid' => $mid, 'conferenceid' => $conferenceid));
        return $json;
    }

    /*
     * 根据userno串，返回name串
     * @param string $usernolist  userno串 1,2,3,4
     * @param string $name 一般为username 即返回相应userno串的username
     * @param int enterpriseno 
     * @param bool $first 是否返回第一个
     */

    public function get_user_info($usernolist, $name, $enterpriseno, $first = false) {
        Doo::loadModel("EnterpriseUseremployee");
        $user = new EnterpriseUseremployee();
        if (empty($usernolist))
            return '';
        $usernolist = array_filter(explode(',', $usernolist));
        $list = $user->find(array("asArray" => true, 'where' => 'enterpriseno=' . $enterpriseno . ' AND userno IN (' . implode(',', $usernolist) . ')'));
        if ($list === false)
            return "";
        $names = array();
        foreach ($list as $value) {
            array_push($names, $value[$name]);
        }
        if ($first) {
            return $names[0];
        }
        return implode(',', $names);
    }

    /*
     * 电话会议详情 
     * @param int $conferenceid 电话会议id
     */

    public function get_phone_meeting_detail($enterpriseno, $meetingid, $conferenceid) {
        Doo::loadClass("Meeting");
        Doo::loadClass("Common");
        $value = Meeting::get_meeting_byid($meetingid, $enterpriseno);
        if ($value === false) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $status = 0;
        if (Common::date_contrast($value->starttime) > 0) {
            $status = 0;  //未开始
        } else if (Common::date_contrast($value->starttime) < 0 && Common::date_contrast($value->endtime) > 0) {
            $status = 1;  //进行中
        } else {
            $status = 2; //已结束
        }
        Doo::loadModel('EnterpriseMeetingPhone');
        $info = new EnterpriseMeetingPhone();
        $info->mid = $meetingid;
        $info->conferenceid = $conferenceid;
        $res = $info->find();
        $content = array();
        foreach ($res as $v) {
            if (!empty($v->username)) {
                $content[] = array(
                    "meetingid" => $value->meetingid,
                    "meetingname" => $value->subject,
                    "meetingdate" => $value->starttime,
                    "starttime" => $value->starttime,
                    'place' => $value->meetingroomname,
                    "endtime" => $value->endtime,
                    "hostname" => $value->presenteruserno, //implode(",", $avatars),
                    "status" => $status,
                    "userno" => $v->userno,
                    'username' => $v->username,
                    'telphone' => $v->telphone
                );
            }
        }
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $content);
        return $json;
    }

    /*
     * 获取计划列表  1、构造计划列表sql条件 2、判断lasttime后是否有更新 3有更新 返回列表数据 否 返回空
     */

    public function get_plan_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass("Plan");
        Doo::loadClass("Exterprise");
        Doo::loadClass("Enum");
        Doo::loadModel('EnterprisePlans');
        $normal = Enum::getStatusType('Normal');
        $plans = new EnterprisePlans();
        $nowtime = date('Y-m-d H:i:s');
        $opt = array(
            'where' => 'userno=? AND enterpriseno=? AND status=?',
            'param' => array($userno, $enterpriseno, $normal),
            'desc' => 'createtime'
        );
        if ($lasttime != -1 && $pageindex == 1) {
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $param = $opt['param'];
            array_push($param, $updatetime, $nowtime);
            $tem_opt = array(
                'where' => $opt['where'] . ' AND updatetime BETWEEN ? AND ?',
                'param' => $param
            );
            $count = $plans->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = $plans->count($opt);
        $page = ($pageindex - 1) * $pagesize;
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $result = $plans->find($opt);
        $list = array();
        foreach ($result as $value) {
            $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$value->userno} AND enterpriseno={$enterpriseno}"));
            $username = $userinfo->employeename;
            $readstatus = Plan::get_unread_amount($value);
            $list[] = array(
                "planid" => $value->planid,
                "title" => $value->title,
                "content" => $value->content,
                "type" => $value->type,
                "starttime" => $value->starttime,
                "endtime" => $value->endtime,
                "userno" => $value->userno,
                "username" => $username,
                "status" => $value->status,
                "createtime" => $value->createtime,
                "readstatus" => $readstatus,
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 计划详情
     */

    public function get_plan_detail($planid) {
        Doo::loadClass("Enum");
        Doo::loadClass("Comment");
        Doo::loadClass("Exterprise");
        Doo::loadClass("Plan");
        $value = Plan::import_data($planid, null, 'EnterprisePlans', 'EnterprisePlanitems');
        if ($value == false) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$value->userno}"));
        $username = $userinfo->employeename;
        $postmode = Enum::getCommentMode('Plan');
        $status = Enum::getStatusType('Normal');
        $opt['where'] = 'postmode=? AND status=? AND postid=?';
        $opt['param'] = array($postmode, $status, $planid);
        $first_comment = Comment::getOneComment($opt);
        $comment_count = Comment::coutComments($opt);
        if ($first_comment != null) {
            $first_comment = $first_comment['username'] . ":" . $first_comment['content'];
        }
        $tousers = explode(',', $value->tousers);
        $prior_users = implode(',', array_slice($tousers, 0, 3));
        $item = $value->item_data;
        $content = array(
            "planid" => $value->planid,
            "title" => $value->title,
            "content" => $value->content,
            "starttime" => $value->starttime,
            "endtime" => $value->endtime,
            "userno" => $value->userno,
            "username" => $username,
            "tousers" => $value->tousers,
            'cctousers' => $value->cctousers,
            'ccreadusers' => $value->ccreadusers,
            'prior_users' => $prior_users,
            'firstcomment' => $first_comment,
            'commentcount' => $comment_count,
            "readusers" => $value->readusers,
            "attachments" => $value->attachments,
            "createtime" => $value->createtime,
            'planitem' => $item
        );
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $content);
        return $json;
    }

    /*
     * 添加或修改计划
     * @param $planid 计划id 添加不需传 修改时传
     * @param $title 计划标题
     * @param $content 计划内容
     * @param $type 类型
     * @param $tousers 收件人
     * @param $cctousers 抄送人
     * @param $attachmentid 附件id
     */

    public function edit_plan($planid, $userno, $enterpriseno, $title, $content, $type, $starttime, $endtime, $tousers, $cctousers, $attachmentid,$item) {
        Doo::loadClass("Exterprise");
        Doo::loadClass('Plan');
        Doo::loadModel('EnterprisePlans');
        $userinfo = Exterprise::getUserInfo(array('where' => "userno={$userno}"));
        $planInfo = new EnterprisePlans();
        $planInfo->type = $type;
        $planInfo->title = $title;
        $planInfo->content = $content;
        $planInfo->tousers = $tousers;
        $planInfo->starttime = $starttime;
        $planInfo->endtime = $endtime;
        $planInfo->userno = $userno;
        $planInfo->enterpriseno = $enterpriseno;
        $planInfo->attachments = $attachmentid;
        $planInfo->createtime = date(DATE_ISO8601);
        $planInfo->updatetime = date(DATE_ISO8601);
        $planInfo->cctousers = $cctousers;
        $planInfo->fanscount = 0;
        $planInfo->viewpermission = 1;
        $planInfo->viewusers = '';
        $planInfo->status = 1101;
        $planInfo->from = 1;
        if (empty($planid)) {
            $feed_do_id = 27;
            $sys_do_id = 13;
            $planid = $planInfo->insert();
            $planInfo->planid = $planid;
        } else {
            //这里是修改计划的
            $planInfo->planid = $planid;
            $feed_do_id = 28;
            $sys_do_id = 15;
            $planInfo->viewusers = $planInfo->cctousers = $planInfo->createtime = null;
            $planInfo->update();
        }
        if ($planid <= 0) {
            $json = array('returncode' => self::ERROR_CODE);
            return $json;
        }else{
            if($item){
                $item = json_decode(stripslashes($item),TRUE);
                $planInfo->item_data = $item;
                Plan::addItem($planInfo,'EnterprisePlanitems');
            }
        }
        try {
            //新鲜事
            Doo::loadClass('Feed');
            Feed::publish_feed($planid, 'planid', $feed_do_id, $userno, '来自手机', $enterpriseno, $planInfo->tousers);
            $readusers = empty($cctousers) ? $tousers : $tousers . ',' . $cctousers;
            Doo::loadClass('SysMessage');
            SysMessage::send_sysMessage($planid, 'plan', $sys_do_id, $userno, $userinfo->username, $enterpriseno, '', $readusers);
        } catch (Exception $exc) {
            $exc->getTraceAsString();
        }
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 阅读计划  将当前人员更新为已读状态 如果是抄送人的话同步更新抄送人
     */

    public function read_plan($planid, $userno, $enterpriseno) {
        Doo::loadModel('EnterprisePlans');
        $plan = new EnterprisePlans();
        $plan->planid = $planid;
        $plan->enterpriseno = $enterpriseno;
        $plan_info = $plan->getOne();

        if ($plan_info === false) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }

        $readusers = $plan_info->readusers;
        if ($readusers == '') {
            $plan->readusers = $userno;
            $plan->updatetime = date('Y-m-d H:i:s');
            $plan->update();
        } elseif (strstr("$userno", $readusers) === false) {
            $arr = explode(',', $readusers);
            array_push($arr, $userno);
            $plan->readusers = implode(',', $arr);
            $plan->updatetime = date('Y-m-d H:i:s');
            $plan->update();
        }
        $cctousers = $plan_info->cctousers;
        $ccreadusers = $plan_info->ccreadusers;
        $pos = strpos($cctousers, $userno);
        if ($pos !== false) {
            if ($ccreadusers == '') {
                $plan->ccreadusers = $userno;
                $plan->update();
            } elseif (strstr("$userno", $ccreadusers) === false) {
                $plan->ccreadusers = $ccreadusers . ',' . $userno;
                $plan->update();
            }
        }
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 批阅计划列表
     */

    public function get_review_plan_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass("Exterprise");
        Doo::loadClass("Enum");
        Doo::loadModel('EnterprisePlans');
        $planInfo = new EnterprisePlans();
        $normal = Enum::getStatusType('Normal');
        $opt = array(
            'where' => '(FIND_IN_SET(?,tousers) or FIND_IN_SET(?,cctousers)) AND enterpriseno=? AND status=?',
            'param' => array($userno, $userno, $enterpriseno, $normal),
            'desc' => 'createtime'
        );
        if ($lasttime != -1 && $pageindex == 1) {
            $nowtime = date('Y-m-d H:i:s');
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $param = $opt['param'];
            array_push($param, $updatetime, $nowtime);
            $tem_opt = array(
                'where' => $opt['where'] . ' and updatetime between ? and ?',
                'param' => $param
            );
            $count = $planInfo->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' =>self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = $planInfo->count($opt);
        $page = ($pageindex - 1) * $pagesize;
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $result = $planInfo->find($opt);
        $list = array();
        foreach ($result as $value) {
            $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$value->userno} AND enterpriseno={$enterpriseno}"));
            $username = $userinfo->employeename;
            $list[] = array(
                "planid" => $value->planid,
                "title" => $value->title,
                "content" => $value->content,
                "type" => $value->type,
                "starttime" => $value->starttime,
                "endtime" => $value->endtime,
                "userno" => $value->userno,
                "username" => $username,
                "status" => $value->status,
                "createtime" => $value->createtime,
                "readstatus" => strstr($value->readusers, "$userno") ? 1 : 0
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 全部计划列表
     */

    public function get_allplan_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass("Exterprise");
        Doo::loadClass("Enum");
        Doo::loadClass("Permission");
        Doo::loadModel('EnterprisePlans');
        $planInfo = new EnterprisePlans();
        $normal = Enum::getStatusType('Normal');
        $admin = Permission::checkUserPermission("Plan", "Admin",$userno,$enterpriseno); //管理
        $opt = array(
            'where'=>'(1=? or userno=? or viewpermission=0 or find_in_set(?,cctousers) or (viewpermission=1 and find_in_set(?,tousers)) or (viewpermission=2 and find_in_set(?,viewusers))) and enterpriseno=?',
            'param'=>array($admin,$userno, $userno, $userno, $userno,$enterpriseno),
            'desc' => 'createtime'
        );
        if ($lasttime != -1 && $pageindex == 1) {
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $nowtime = date('Y-m-d H:i:s');
            $param = $opt['param'];
            array_push($param, $updatetime, $nowtime);
            $tem_opt = array(
                'where' => $opt['where'] . ' AND updatetime BETWEEN ? AND ?',
                'param' => $param
            );
            $count = $planInfo->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = $planInfo->count($opt);
        $page = ($pageindex - 1) * $pagesize;
        $opt['where'] .= " and status=$normal";
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $result = $planInfo->find($opt);
        $list = array();
        foreach ($result as $value) {
            $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$value->userno} AND enterpriseno={$enterpriseno}"));
            $username = $userinfo->employeename;
            $list[] = array(
                "planid" => $value->planid,
                "title" => $value->title,
                "content" => $value->content,
                "type" => $value->type,
                "starttime" => $value->starttime,
                "endtime" => $value->endtime,
                "userno" => $value->userno,
                "username" => $username,
                "status" => $value->status,
                "createtime" => $value->createtime,
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }
    /*
     * 计划/汇报  删除计划/汇报
     */
    public function delete_plan($model,$id){
        Doo::loadClass('Plan');
        switch ($model){
            case 'plan':
                $modelname = 'EnterprisePlans';
                break;
            case 'report':
                $modelname = 'EnterpriseReports';
                break;
            default :
                $json = array('returncode'=>self::ARG_ERROR_CODE);
                return $json;
        }
        Plan::delete_plan($modelname, $id);
        $json = array('returncode'=>self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 申请审批 我的申请列表
     */

    public function my_apply_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass('Process');
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        $page = ($pageindex - 1) * $pagesize;
        $opt['where'] = "enterpriseno={$enterpriseno}";
        $nowtime = date('Y-m-d H:i:s');

        if ($lasttime != -1 && $pageindex == 1) {
            $lasttime = date('Y-m-d H:i:s', $lasttime);
            $tem_opt['where'] = $opt['where'] . " AND updatetime BETWEEN '$lasttime' AND '" . $nowtime . "'";
            $count = Process::countMyCaseList($userno, $tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $opt = array(
            'where'=>"userno={$userno} AND status={$normal} AND enterpriseno={$enterpriseno}",
            'limit' => ($page < 0 ? 0 : $page) . ',' . $pagesize,
            'asc' => 'state',
            'desc' => 'createtime'
        );
        $res = Process::findMyCaseList($userno, $opt);
        $count = Process::countMyCaseList($userno, false);
        $list = array();
        foreach ($res as $value) {
            $list[] = array(
                "priority" => $value['priority'],
                'id' => $value['caseid'],
                "name" => $value['name'],
                "status" => $value['state'],
                "createtime" => $value['createtime'],
                "updatetime" => $value['updatetime'],
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }
    /*
     * 申请审批  待我审批
     */
    public function get_docase_list($userno,$enterpriseno,$pageindex,$pagesize,$lasttime){
        Doo::loadClass("Process");
        Doo::loadClass("Enum");
        Doo::loadClass('Exterprise');
        $page = ($pageindex - 1) * $pagesize;
        $opt['where'] = "enterpriseno={$enterpriseno}";
        if ($lasttime != -1 && $pageindex == 1) {
            $nowtime = date('Y-m-d H:i:s');
            $updatetime = date('Y-m-d H:i:s',$lasttime);
            $tem_opt['where'] = $opt['where'] . " AND updatetime BETWEEN '$updatetime' AND '" . $nowtime . "'";
            $count = Process::countToDo($userno, $tem_opt);
            if ($count == 0) {
                $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>array());
                return $json;
            }
        }
        //$opt['asc'] = 'state';
        $opt["desc"] = "createtime";
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $todocaselist = Process::getToDoList($userno, $opt);
        $count = Process::countToDo($userno, $opt);
        $list = array();
        foreach ($todocaselist as $value) {
            $username = '';
            $user = Exterprise::getUserInfo(array('where' => 'userno=' . $value['userno']));
            if (count($user) > 0) {
                $username = $user->username;
            }
            $state = $value['state'];
            if ($state == 2) {
                if (strpos($value['usernolist'], $userno) !== false) {
                    $state = 1;
                }
            }
            $list[] = array(
                "caseid" => $value['caseid'],
                "priority" => $value['priority'],
                "createtime" => $value['createtime'],
                "creator" => $username, //in_tousers(currentuser.@userno,todocaselist' value.usernolist)
                "status" => $state,
                "updatetime" => $value['updatetime'],
                "name" => $value['name']
                );
        }
        $now = time();
        $json = array('returncode' =>self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 申请审批  获取我能申请的申请单
     * 需要注意问题：申请单的申请权限
     */

    public function get_case_list($userno, $enterpriseno) {
        Doo::loadClass('FormSystem');
        Doo::loadClass("Params");
        Doo::loadClass("Enum");
        Doo::loadClass('Process');
        $type_list = Params::findParams($enterpriseno, 0, Enum::get_paramtype("form", true));
        $typename = array();
        foreach ($type_list as $type) {
            $typename["{$type['paramvalue']}"] = $type['paramname'];
        }
        $status = Enum::getStatusType("Normal");
        $opt_item['where'] = "enterpriseno=$enterpriseno AND dynaformid=? AND isdelete=0 ";
        $opt_form["where"] = "enterpriseno=$enterpriseno AND status=$status";
        $res = FormSystem::getDynaform($opt_form);
        $list = array();
        foreach ($res as $value) {
            if (!Process::isCanView($userno, $value->canviewusers, $value->canviewdepartment, $value->canviewposition)) {
                continue;
            }
            $opt_item['param'] = array($value->dynaformid);
            $opt_item['asc'] = 'sort';
            $data = array();
            $formdata = FormSystem::getDynaformitem($opt_item);
            if ($formdata == null) {
                continue;
            } else {
                foreach ($formdata as $v) {
                    $data[] = array(
                        'type' => $v->type,
                        'name' => $v->name,
                        'nameid' => $v->tdname,
                        'data' => $v->defaultvalue,
                        'require' => $v->required
                    );
                }
            }
            $list[] = array(
                'formid' => $value->dynaformid,
                'remark' => $value->remark,
                'formname' => $value->name,
                'formtype' => isset($typename["$value->type"]) ? $typename["$value->type"] : '其他',
                'formtypeid' => $value->type,
                'formdata' => $data
            );
        }
        $json = array('returncode' => self::SUCCESS_CODE, 'contents' => $list);
        return $json;
    }
    /*
     * 申请审批  新建审批申请
     * @param $name 申请单名
     * @param $processid 流程id
     * @param $dynaformid 表单id
     * @param $priority 优先级（0=普通，1=重要，2=紧急）
     * @param $mode 操作方式
     * @param $nextStepid 下一步id
     * @param $approvers 审批人,多用,号分开
     * @param $remark 说明
     * @param $pass 流转条件
     * @param $sign 签名id
     * @param $attachmentid 附件id
     * @param $mode 操作方式
     * @param $param 
     */
    public function edit_case($userno,$enterpriseno,$name,$processid,$dynaformid,$priority,$nextStepid,$approvers,$remark,$pass,$sign,$attachmentid,$mode,$param,$form_abstract,$applicant_data){
        Doo::loadClass('FormSystem');
        Doo::loadClass('Process');
        $opt['where'] = 'dynaformid = ?';
        $opt['param'] = array($dynaformid);
        $model = FormSystem::getOneDynaform($opt);
        $opt['where'].= ' AND isdelete=0';
        $itemmodel = FormSystem::getDynaformitem($opt);
        //添加相应的业务
        $tbname = $model->tbname;
        $name = $model->name;
        $res = Process::addProcessCase($userno, $enterpriseno, $name, $processid, $dynaformid, $priority, $approvers, $nextStepid,$mode,'',1,$pass,$remark,$attachmentid,$sign,$form_abstract,'form',$applicant_data);
        if ($res["success"] == false) {
            $json = array('returncode'=>self::ERROR_CODE,'message'=>$res['message']);
            return $json;
        }
        $caseid = $res["data"];
        $sql = "insert into $tbname (";
        $names = array();
        $values = array();
        array_push($names, "userno");
        array_push($values, $userno);
        array_push($names, "caseid");
        array_push($values, $caseid);
        foreach ($itemmodel as $value) {
            if ($value->type == 'applicat')
                continue;
            if ($value->type == 'date_1_1' || $value->type == 'date_2_1' || $value->type == 'compound') {
                array_push($names, $value->tdname . '_1');
            }
            array_push($names, $value->tdname);
            if (isset($param[$value->tdname])) {
                if ($value->tdtype == "int" || $value->tdtype == "float") {
                    if ($value->type == 'compound') {
                        array_push($values, "'" .$param[$value->tdname . '_1'] . "'");
                    }
                    array_push($values, "'" .$param[$value->tdname] . "'");
                } else {
                    if ($value->type == 'date_1_1' || $value->type == 'date_2_1') {
                        $v = $param[$value->tdname . '_1'];
                        array_push($values, "'" . $v . "'");
                    }
                    $v = $param[$value->tdname];
                    array_push($values, "'" . $v . "'");
                }
            } else {
                array_push($values, $value->tddefaultvalue);
            }
        }
        $sql.=implode(",", $names) . ") values (";
        $sql.=implode(",", $values) . ")";
        Doo::db()->query($sql);
        $json = array('returncode'=>self::SUCCESS_CODE);
        return $json;
    }
    /*
     * 申请审批  获取审批表单流程
     * @param $formid表单id
     * @return  processid流程id,processname流程名字,remark流程说明,first_step_user第一步操作人,mode操作方式
     */
    public function get_form_process($userno,$enterpriseno,$formid){
        Doo::loadClass('Process');
        Doo::loadClass('Enum');
        $processlist = Process::findProcesslistByFormid($formid,$userno);
        $step_data = array();
        foreach ($processlist as $k => $v) {
            if ($v['processid'] == 0) {
                $step_data [] = array('processid' => 0, 'processname' => '自由流程', 'remark' => "", 'first_step_user' => '');
                continue;
            }
            $process = Process::findProcess(array('where' => 'processid=' . $v['processid'], 'limit' => 1));
            $step = Process::findProcessSteps(array('where' => 'processid=' . $v['processid']));
            if (count($step) == 1)
                continue;
            $approvers = Process::getStepApprovers(0, $step[1]->stepid, $userno, $enterpriseno);
            $tem_peo = "";
            foreach ($approvers as $g) {
                $tem_peo .= $g['userno'] . ",";
            }
            $step_data [] = array('processid' => $process->processid, 'processname' => $process->name, 'remark' => $process->remark, 'first_step_user' => trim($tem_peo, ','),'mode'=>$step[1]->mode);
        }
        $freeStep = array();
        $status = Enum::getStatusType('Normal');
        $steptypelist = Process::findProcessStepType(array("where" => "(enterpriseno=0 or enterpriseno=$enterpriseno) and status=$status"));
        foreach ($steptypelist as $k => $v) {
            $freeStep[] = array('name' => $v->name, 'steptypeid' => $v->steptypeid);
        }
        $json = array('returncode'=>self::SUCCESS_CODE,'data'=>$step_data,'freestep'=>$freeStep);
        return $json;
    }
    /*
     * 申请审批  进行审批操作
     * @param $caseid 审批单号
     * @param $state 审批单状态
     * @param $remark 审批意见
     * @param $pass 流转条件
     * @param $sign 签名附件id
     * @param $attachmentid 附件id
     * @param $rollbackid 可回退步骤id
     * @param $rollbackname 可回退步骤名
     * @param $nextapprover 下一步操作人
     * @param $nextsteptype 下一步操作步骤类型
     * @param $mode 操作类型
     */
    public function handle_case($caseid,$state,$remark,$enterpriseno,$userno,$pass,$sign,$attachmentid,$rollbackid,$rollbackname,$nextapprover,$nextsteptype,$mode){
        Doo::loadClass("Process");
        if ($state == 0) {//不同意
            $state = Process::$PROCESS_STATE_REJECT;
        } else {//同意或已阅读
            $state = Process::$PROCESS_STATE_SUCCESS;
        }
        if (mb_strlen($remark,'utf-8') > 100) {
            $remark = mb_substr($remark, 0, 100,'utf-8');
        }
        $res = Process::handleProcessCase($caseid, $userno, $enterpriseno, $remark, $state, $nextapprover, $nextsteptype, $attachmentid,$rollbackid,$rollbackname, '',1, $pass,$mode,$sign);
        if($res['success']){
            $json = array('returncode'=>self::SUCCESS_CODE);
        }else{
            $json = array('returncode'=>self::ERROR_CODE,'message'=>$res['message']);
        }
        return $json;
    }
    /*
     * 申请审批  审批催办
     * $caseid 审批单号
     */
    public function remind_process($userno,$enterpriseno,$caseid){
        Doo::loadClass("Process");
        $caseinfo = Process::getProcessCaseInfo(array("where" => "caseid=$caseid"));
        if ($caseinfo == null || $caseinfo === FALSE) {
            $json = array('returncode'=>self::NO_PERMISSION_CODE);
            return $json;
        }
        $caseiteminfo = Process::getProcessCaseItemInfo(array("where" => "caseid=$caseid"));
        if ($caseiteminfo != false && !empty($caseiteminfo->usernolist) && $caseiteminfo->usernolist != null && $caseiteminfo->usernolist != "null") {
            Doo::loadClass('Exterprise');
            $user = Exterprise::getUserInfo(array('where' => 'userno=' . $userno));
            $username = ($user === false ? '' : $user->username);
            Doo::loadClass('SysMessage');
            $target_userids = $caseiteminfo->usernolist;
            SysMessage::send_sysMessage($caseid, 'process', SysMessage::$Do_process_reminders, $userno, $username, $enterpriseno, '', $target_userids, 0);
        }
        $json = array('returncode'=>self::SUCCESS_CODE);
        return $json;
    }
    /*
     * 申请审批详情  
     * 审批状态 status （1:待审批  2:审批中   3:已通过  4:不通过 5:已撤销）
     * @return $array = (
     *      casename 申请单名字
     *      creatorid  表单申请人userno
     *      creator   表单申请人username
     *      createtime  表单创建时间
     *      status  表单状态
     * )
     * 第一步 拿表单内容 第二部获取表单审批流程每个步骤的详情 
     * 
     */

    public function get_case_detail($userno, $enterpriseno, $caseid) {
        Doo::loadClass("Process");
        Doo::loadClass("Enum");
        Doo::loadClass("FormSystem");
        Doo::loadClass('Attachment');
        Doo::loadClass('Exterprise');
        Doo::loadClass('Comment');
        $normal = Enum::getStatusType('Normal');
        //所查数据表 workflow_cases
        $caseinfo = Process::getProcessCaseInfo(array('where' => 'caseid=? AND status=?', 'param' => array($caseid, $normal)));
        if ($caseinfo === false) {
            $json = array('reutrncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $applicant_data = $caseinfo->applicant_data;
        if(!empty($applicant_data)){
            $arr = explode(",", $applicant_data);
            $applicatuser = trim($arr[0],'u:');//申请人userno
        }else{
            $applicatuser = $caseinfo->userno;
        }
        //审批评论
        $postmode = Enum::getCommentMode('Process');
        $com['where'] = 'postmode=? AND status=? AND postid=?';
        $com['param'] = array($postmode, $normal, $caseid);
        $first_comment = Comment::getOneComment($com);
        $comment_count = Comment::coutComments($com);
        if ($first_comment != null) {
            $first_comment = $first_comment['username'] . ":" . $first_comment['content'];
        }else{
            $first_comment = '';
        }
        $creator = Exterprise::getUserInfo(array('where' => 'userno=' . $caseinfo->userno));
        $form_data = array();
        $item_data = array(
            'casename' => $caseinfo->name,        //表单名
            'creatorid' => $caseinfo->userno,
            'creator' => $creator->username,       //创建人名
            'createtime' => $caseinfo->createtime,  //创建时间
            'status' => $caseinfo->state,         //审批状态   
            'abstract'=>$caseinfo->abstract,      //摘要
            'firstcomment'=>$first_comment,       //第一条评论
            'commentcount'=>$comment_count,       //评论条数
            'applicantdata'=>$applicant_data      //申请人信息
        );

        $dynaformid = $caseinfo->dynaformid;
        $processid = $caseinfo->processid;
        //获取动态表单信息  后面用到字段是tbname 生成数据库表名  所查数据表 workflow_dynaform
        $dynaform = FormSystem::getOneDynaform(array('where' => 'dynaformid=? AND enterpriseno=?', 'param' => array($dynaformid, $enterpriseno)));
        if ($dynaform == null) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $tbname = $dynaform->tbname;
        $opt_item = array(
            'where' => 'dynaformid=? AND enterpriseno=? AND isdelete=?',
            'param' => array($dynaformid, $enterpriseno, 0)
        );
        //workflow_dynaformitem为各个控件的详情
        $items = FormSystem::getDynaformitem($opt_item);   //申请单元素
        //查询表单生成数据表tbname相应的内容
        $tbname_details = Doo::db()->fetchAll("SELECT * FROM {$tbname} WHERE caseid=$caseid ");
        if ($tbname_details != null) {
            $tb_detail = $tbname_details[0];
            /*
             * tbname在workflow_dynaform中 各个控件对应tbname中的字段为workflow_dynaformitem中的tdname的值
             * 申请单元素  type为控间类型 
             * applicat为申请人控件值为workflow_cases中的userno
             * select radio 分别为下拉和单选  1=>a,2=>b  1
             * checkbox 复选  1=>a,2=>b  1,2
             * selection 选人控件
             * date_1_1 date_2_1 compound 日期控件 符合控件
             * dataout
             */
            foreach ($items as $item) {
                if ($item->type == 'applicat') {
                    $form_data[] = array('name' => $item->name, 'content' => $applicatuser);
                }
                if (isset($tb_detail[$item->tdname])) {
                    if (in_array($item->type, array('select', 'radio'))) {
                        $obj = json_decode(htmlspecialchars_decode($item->defaultvalue));
                        if (is_object($obj) || is_array($obj)) {
                            foreach ($obj as $v1) {
                                if ($v1->objValue == $tb_detail[$item->tdname]) {
                                    $form_data[] = array('name' => $item->name, 'content' => $v1->objName);
                                }
                            }
                            if (empty($tb_detail[$item->tdname])) {
                                $form_data[] = array('name' => $item->name, 'content' => '');
                            }
                        }
                    } elseif ($item->type == 'checkbox') {
                        $obj = json_decode(htmlspecialchars_decode($item->defaultvalue));
                        $checkbox_data = "";
                        if (is_object($obj) || is_array($obj)) {
                            foreach ($obj as $v1) {
                                if (in_array($v1->objValue, explode(',', $tb_detail[$item->tdname])))
                                    $checkbox_data.= $v1->objName . ',';
                            }
                        }
                        $form_data[] = array('name' => $item->name, 'content' => trim($checkbox_data, ','));
                    }elseif ($item->type == 'selection') {
                        $username = $this->get_user_info($tb_detail[$item->tdname], 'username', $enterpriseno, $first = false);
                        $form_data[] = array('name' => $item->name, 'content' => $username);
                    } else if ($item->type == 'date_1_1' || $item->type == 'date_2_1') {
                        $content = $tb_detail[$item->tdname];
                        $content .= '至'.$tb_detail[$item->tdname . '_1'];
                        $form_data[] = array('name' => $item->name, 'content' => $content);
                    }else if($item->type == 'compound'){
                        $obj = json_decode(htmlspecialchars_decode($item->defaultvalue));
                        $content = $tb_detail[$item->tdname];
                        if (is_object($obj) || is_array($obj)) {
                            foreach ($obj as $v1) {
                                if ($v1->objValue == $tb_detail[$item->tdname . '_1']) {
                                    $content.=$v1->objName;
                                    break;
                                }
                            }
                        }
                        $form_data[] = array('name' => $item->name, 'content' => $content);
                    }else if ($item->type == 'dataout') {
                        $form_data[] = array('name' => $item->name, 'content' => $tb_detail[$item->tdname]);
                    }else if($item->type == 'table'){
                        $form_data[] = array('name' => $item->name, 'content' =>'','dynaformitemid'=>$item->dynaformitemid);
                    } 
                    else {
                        $form_data[] = array('name' => $item->name, 'content' => $tb_detail[$item->tdname]);
                    }
                }
            }
        }

        $casehistorylist = Process::findProcessCaseHistory($caseid, $caseinfo->processid, array("where" => "caseid=$caseid", "asc" => "number"));
        $step_result = array();
        if ($casehistorylist !== false) {
            foreach ($casehistorylist as $value) {
                if(!empty($casehistorylist[0]['operator']) && $value['number'] == 1){
                    $operator = $casehistorylist[0]['operator'];
                }else{
                    $operator = '';
                }
                if ($processid > 0 && isset($value['stepid'])) {
                    $historylist = Process::findItemhistory(array('where' => 'caseid=' . $caseinfo->caseid . ' AND stepid=' . $value['stepid'], 'asArray' => true));
                    foreach ($historylist as $value1) {
                        $username = '';
                        if (!empty($value1['userno'])) {
                            $this->find_users_name($username, $value1['userno'], $enterpriseno);
                        }
                        $step_result[] = array(
                            'pname' => $value['name'],
                            'user' => $username,
                            'time' => $value1['createtime'],
                            'remark' => strip_tags($value1['remark']),
                            'attachmentids' => $value1['attachmentids'],
                            'signaturepath' => $value1['signaturepath'],
                            'operator'=>$operator
                        );
                    }
                } else {
                    $username = '';
                    if (!empty($value['userno'])) {
                        $this->find_users_name($username, $value['userno'], $enterpriseno);
                    }
                    $step_result[] = array('pname' => $value['name'],
                        'user' => $username,
                        'time' => $value['createtime'],
                        'remark' => strip_tags($value['remark']),
                        'attachmentids' => $value['attachmentids'],
                        'signaturepath' => $value['signaturepath'],
                        'operator'=>$operator
                    );
                }
            }
        }
        $iteminfo = Process::getProcessCaseItemInfo(array("where" => "caseid=$caseid"));
        $currstep = $iteminfo->stepid;
        $approvers = array(); //可以选择的人员
        $mod1 = 0; //同意选项选人
        ////0为不选人, 1为自由节点,2为固定流程(当前节点是普通,下一节点是会签),3为固定流程(当前节点是普通,下一节点是普通),
        //4为固定流程(当前节点是会签,下一节点是会签),5为固定流程(当前节点是会签,下一节点是普通),
        $mod2 = 0; //不同意选是否选人
        $canop = 0; //是否允许操作(同意，不同意)

        $currapprovers = explode(',', $iteminfo->usernolist);
        if (in_array($userno, $currapprovers)) {
            $canop = 1;
        }
        /**
         * 注意
         * 1. 如果是普通流程节点或自由流程节点，跳到下一节点均需要人工指定下一步节点的某个人,
         * 2. 会签节点，则直接为该节点的所有人，不用选审批人
         * 3. 下一步节点是否为会签，如果不是与1相同,否则与2相同
         */
        if ($caseinfo->processid > 0 && $iteminfo->stepid > 0 && ($caseinfo->state == Process::$PROCESS_STATE_APPROVEING || $caseinfo->state == Process::$PROCESS_STATE_WAITING)) {
            //固定流程 获取该固定流程的详细信息
            $processinfo = Process::getProcessCaseInfo(array('where' => 'processid=' . $caseinfo->processid));
            if ($processinfo === false) {
                $json = array('returncode' => self::NO_RECODE_CODE);
                return $json;
            }
            //流程当前步骤详细信息
            $stepinfo = Process::getProcessStepsInfo(array('where' => 'stepid=' . $iteminfo->stepid));
            if ($stepinfo === false) {
                $json = array('returncode' => self::NO_RECODE_CODE);
                return $json;
            }
            switch ($stepinfo->mode) {
                case 2: //当前节点是会签
                    //1.同意选项
                    $gonext = Process::isPassedProcessStep($caseinfo->caseid, $caseinfo->processid, $iteminfo->stepid, Process::$PROCESS_STATE_SUCCESS, $caseinfo->userno, $enterpriseno);
                    //2.不同意选项
                    $notonext = Process::isPassedProcessStep($caseinfo->caseid, $caseinfo->processid, $iteminfo->stepid, Process::$PROCESS_STATE_REJECT, $caseinfo->userno, $enterpriseno);
                    if ($gonext || $notonext) {
                        $nextsteps = Process::findProcessSteps(array("where" => 'processid=' . $caseinfo->processid . ' AND stepid > ' . $iteminfo->stepid, "limit" => 1, 'asc' => 'number'));
                        if ($nextsteps === false) {
                            //该节点是最后一个节点
                            $mod1 = 0; //不选人
                            $mod2 = 0; //不选人
                        } else {
                            if ($nextsteps->mode == 2) {
                                //会签不选人
                                $userlistarray = Process::getStepApprovers($caseid, $nextsteps->stepid, $caseinfo->userno, $enterpriseno);
                                if (count($userlistarray) > 0) {
                                    $usernamelist = array();
                                    foreach ($userlistarray as $v) {
                                        array_push($usernamelist, $v["username"]);
                                    }
                                    $countersign_users = implode(',', $usernamelist);
                                }

                                $mod1 = 0; //不选人
                                $mod2 = 0; //不选人
                            } else {
                                //下一节点非会签,则需要选择下一审批人
                                $approvers = Process::getStepApprovers($caseinfo->caseid, $nextsteps->stepid, $caseinfo->userno, $enterpriseno);
                                $mod1 = 1;
                                if ($notonext) {
                                    $mod2 = 1;
                                }
                            }
                        }
                    }
                    break;
                default://当前节点非会签
                    //1.同意选项
                    $nextsteps = Process::findProcessSteps(array("where" => 'processid=' . $caseinfo->processid . ' AND stepid > ' . $iteminfo->stepid, "limit" => 1));
                    if ($nextsteps === false) {
                        $mod1 = 0; //不选人
                        $mod2 = 0; //不选人
                    } else {
                        //下一步节点是会签
                        if ($nextsteps->mode == 2) {
                            $userlistarray = Process::getStepApprovers($caseid, $nextsteps->stepid, $caseinfo->userno, $enterpriseno);
                            if (count($userlistarray) > 0) {
                                $usernamelist = array();
                                foreach ($userlistarray as $v) {
                                    array_push($usernamelist, $v["username"]);
                                }
                                $countersign_users = implode(',', $usernamelist);
                            }
                            $mod1 = 0; //不选人
                            $mod2 = 0; //不选人
                        } else {
                            //审批步骤人员
                            $approvers = Process::getStepApprovers($caseid, $nextsteps->stepid, $caseinfo->userno, $enterpriseno);
                            $mod1 = 1; //选人
                            $mod2 = 0; //不选人
                        }
                    }
                    break;
            }
        }//jefflee 20130106
        else if ($caseinfo->processid == 0 && $iteminfo->freeflowstepid > 0 && ($caseinfo->state == Process::$PROCESS_STATE_APPROVEING || $caseinfo->state == Process::$PROCESS_STATE_WAITING)) {
            $stepinfo = Process::getFreeflowStep(array('where' => 'stepid=' . $iteminfo->freeflowstepid));
            if ($stepinfo === false) {
                $json = array('returncode' => self::NO_RECODE_CODE);
                return $json;
            }
            switch ($stepinfo->mode) {
                case 2: //当前节点是会签
                    //1.同意选项
                    $gonext = Process::isPassedProcessStep($caseinfo->caseid, $caseinfo->processid, $iteminfo->freeflowstepid, Process::$PROCESS_STATE_SUCCESS, $caseinfo->userno, $enterpriseno);
                    //2.不同意选项
                    $notonext = Process::isPassedProcessStep($caseinfo->caseid, $caseinfo->processid, $iteminfo->freeflowstepid, Process::$PROCESS_STATE_REJECT, $caseinfo->userno, $enterpriseno);
                    //var_dump($gonext);var_dump($notonext);exit;
                    //用户同意后是否显示选人
                    if ($gonext) {
                        $mod1 = 1; //选人
                        $mod2 = 0; //不选人
                    }
                    break;
                default :
                    $mod1 = 1; //选人
                    $mod2 = 0; //不选人
                    break;
            }
        }
        //jeffli 20130217 为了兼容更新前用户提交的自由流程
        else if ($caseinfo->processid == 0 && $iteminfo->freeflowstepid == 0 && ($caseinfo->state == Process::$PROCESS_STATE_APPROVEING || $caseinfo->state == Process::$PROCESS_STATE_WAITING)) {
            $mod1 = 1; //选人
            $mod2 = 0; //不选人
        }
        $status = Enum::getStatusType('Normal');
        $steptypelist = Process::findProcessStepType(array("where" => "(enterpriseno=0 OR enterpriseno=$enterpriseno) AND status=$status"));
        if ($steptypelist) {
            foreach ($steptypelist as $v) {
                $freeStep[] = array('name' => $v->name, 'steptypeid' => $v->steptypeid);
            }
        }
        $item_data['freestep'] = $freeStep;
        $item_data['detail'] = $form_data;
        $item_data['process'] = $step_result;
        $item_data['canopt'] = $canop;
        $item_data['steptype'] = isset($iteminfo) ? $iteminfo->steptypeid : 0;
        $item_data['curuser'] = implode(',', $currapprovers);
        $item_data['approvers'] = $approvers;
        $item_data['agree_member'] = $mod1;
        $item_data['reject_member'] = $mod2;
        $item_data['currstep'] = $currstep;
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $item_data);
        return $json;
    }
    /*
     * 申请审批  获取回退步骤信息
     * 回退信息只有在审批不同意时才会获取
     */
    public function get_caserollback_detail($caseid,$userno,$enterpriseno){
        Doo::loadClass("Process");
        //当前步骤
        $rollback = array();
        $iteminfo = Process::getProcessCaseItemInfo(array('where' => 'caseid=' . $caseid));
        $json = array('returncode'=>self::SUCCESS_CODE,'content'=>'');
        if ($iteminfo == null) {
            return $json;
        } else {
            if ($iteminfo->stepid > 0) {
                $stepinfo = Process::getProcessStepsInfo(array('where' => 'stepid=' . $iteminfo->stepid));
                if ($stepinfo == null) {
                    return $json;
                } else {
                    //会签不支持
                    if ($stepinfo->mode == 2) {
                        return $json;
                    } else {
                        if ($stepinfo->approvers != '') {
                            $approvers = explode(',', $stepinfo->approvers);
                            if (count($approvers) > 1) {
                                return $json;
                            }
                        }
                    }
                }
            } else {
                $stepinfo = Process::getFreeflowStep(array('where' => 'stepid=' . $iteminfo->freeflowstepid));
                if ($stepinfo->mode == 2) {
                    return $json;
                } else {
                    if ($iteminfo->usernolist != '') {
                        $approvers = explode(',', $iteminfo->usernolist);
                        if (count($approvers) > 1) {
                            return $json;
                        }
                    }
                }
            }
        }
        $res = Process::getRollbackStepInfo($caseid, $enterpriseno, $userno, $iteminfo->stepid);
        foreach($res['data'] as $value){
            $roll = array();
            $roll['id'] = $value['stepid'];
            $roll['name'] = $value['name'];
            $roll['username'] = $value['username'];
            $rollback[] = $roll;
        }
        $json['content'] = $rollback;
        return $json;
    }
    /*
     * 申请审批  获取表格控件详情 
     * @return tablehtml 表格的html文本
     */
    public function get_formtable_detail($caseid,$enterpriseno,$dynaformitemid){
        Doo::loadClass("FormSystem");
        Doo::loadClass('Attachment');
        $a = Doo::loadModel('EnterpriseAttachments',true);
        $opt_item = array(
            'where' => 'dynaformitemid=? AND enterpriseno=? AND isdelete=?',
            'param' => array($dynaformitemid, $enterpriseno, 0)
        );
        //workflow_dynaformitem为各个控件的详情
        $item = FormSystem::getOneDynaformitem($opt_item);   //申请单元素
        if($item->type == 'table'){
            $r = FormSystem::get_table_value($caseid, $item->itemdetailed,$enterpriseno);
            $res = json_decode($r, true);
        }
        $info = json_decode($item->itemdetailed,true);
        $tbname = $info['tbname'];
        $tabletitle = '<div class="title"><table border="0" cellspacing="0" cellpadding="0" style="margin-left:10px;">'; //表头信息
        $thhtml = '';
        $tr_arr = array();
        $tdstart = '<td><div>';  //表格td的样式
        $tdend = '</div></td>';
        foreach($info['itemsinfo'] as $key=>$th){
            $thhtml .= $tdstart.$th[1].$tdend;    //表头
            if($th[4] == 2){                     //汇总，表格最后一行
                $query = "SELECT SUM($th[7]) AS total FROM $tbname WHERE caseid=$caseid";
                $data = Doo::db()->fetchAll($query);
                $collect[$key] = '合计:'.$data[0]['total'];
            }  elseif ($th[4] == 3) {
                $query = "SELECT SUM($th[7])/COUNT($th[7]) AS arg FROM $tbname WHERE caseid=$caseid";
                $data = Doo::db()->fetchAll($query);
                $collect[$key] = '平均:'.$data[0]['arg'];
            }else{
                $collect[$key] = '&nbsp;';
            }
            $attribute[$th[7]] = $th[6];
        }
        $lasttd = '';
        foreach($collect as $v){
            $lasttd .= $tdstart.$v.$tdend;
        }
        $lasttrhtml = '<tr>'.$lasttd.'</tr>';
        $tabletitle .= '<tr>'.$thhtml.'</tr></table></div>';//表头信息
        foreach($res as $tr){       //表格内容
            $trhtml = '';
            foreach($tr as $filedid=>$td){
                if(!empty($td)){
                    if(is_array($td)){       //单选复选
                        $trhtml .= $tdstart.$td['selectname'].$tdend;
                    }elseif(isset($attribute[$filedid][2]) && $attribute[$filedid][2] == 'attachment'){  //附件
                        $att = $a->getOne(array('where'=>"attachmentid=$td",'asArray'=>true));
                        $td = $att['filename'];
                        $trhtml .= $tdstart.$td.$tdend;
                    }elseif(isset($attribute[$filedid][2]) && $attribute[$filedid][2] == 'selection'){  //选人控件
                        $username = explode('|', $td);
                        $name = $username[1];
                        $trhtml .= $tdstart.$name.$tdend;
                    }
                    else{   //一般控件
                        $trhtml .= $tdstart.$td.$tdend;
                    }
                }else{   //内容为空
                    $trhtml .= $tdstart.'&nbsp;'.$tdend;
                }
            }
            $tr_arr[] = $trhtml;
        }
        $tabletd = '';
        foreach ($tr_arr as $value){
            $tabletd .= '<tr>'.$value.'</tr>';
        }
        $css = '<style type="text/css">
body,div,ul,li{padding:0px; margin:0px;}
li{ list-style:none;}
body{ text-shadow:0px 1px 0px #FFF; color:#474957; font-size:17px; font-family:sans-serif, "Adobe 黑体 Std R";}
.title{ width:99%; height:50px; border:solid 1px #97a9b2; border-radius:10px; background:-moz-linear-gradient(top,#b9d1e8,#cfdfef); box-shadow:-1px 1px 0px rgba(255,255,255,1) inset; box-shadow:3px 0px 5px rgba(0,0,0,0.19); line-height:50px; position:relative;z-index:10; margin:0 auto;}
.title table td div{ width:90px; text-align:center; border-right:solid 1px #97a9b2; box-shadow:1px 0px 0px rgba(255,255,255,1); display:block;line-height:50px;}
.main{ width:97%; height:100%; border-radius:4px;border:solid 1px #a0afb5;border-bottom:solid 1px #a0afb5; position:relative; z-index:9; top:-5px;box-shadow:0px 0px 20px rgba(0,0,0,0.2);background:white;}
.main table tr{ overflow:hidden;}
.main table td div{width:90px; text-align:center; border-right:solid 1px #97a9b2; border-bottom:solid 1px #97a9b2; box-shadow:1px 0px 0px rgba(255,255,255,0.1); height:60px; line-height:60px; display:inline-block;}
.bg_btm_left{ background:url(bg_btm.png) no-repeat 0px 0px;margin-left:17px; width:100%; height:10px; margin-top:-5px;}
.bg_btm_right{background:url(bg_btm.png) no-repeat right -18px ; width:40%; height:10px; float:right;}
</style>';
        //$css2 = '.main{ width:97%; height:100%; border-radius:4px;border:solid 1px #a0afb5;border-bottom:solid 1px #a0afb5; position:relative; z-index:9; top:-5px;box-shadow:0px 0px 20px rgba(0,0,0,0.2);background:white;}table tr{ overflow:hidden;}table td div{width:90px; text-align:center; border-right:solid 1px #97a9b2; border-bottom:solid 1px #97a9b2; box-shadow:1px 0px 0px rgba(255,255,255,0.1); height:60px; line-height:60px; display:inline-block;}.bg_btm_left{ background:url(bg_btm.png) no-repeat 0px 0px;margin-left:17px; width:100%; height:10px; margin-top:-5px;}.bg_btm_right{background:url(bg_btm.png) no-repeat right -18px ; width:40%; height:10px; float:right;}</style>';
        //$css .= $css2;
        $foothtml = '<div class="bg_btm_left"><div class="bg_btm_right"></div></div>';
        $tablehtml = $css.$tabletitle.'<div class="main" style="margin-left:12px;"><table border="0" cellspacing="0" cellpadding="0" width="">'.$tabletd.$lasttrhtml.'</table></div>'.$foothtml;
		$json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$tablehtml);
        return $json;
    }
    /*
     * 申请审批 检测表单是否可用
     */
    public function check_dynaform_status($dynaformid, $enterpriseno){
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        $form = Doo::loadModel('WorkflowDynaform',true);
        $opt = array(
            'where'=>'dynaformid=? and enterpriseno=?',
            'param'=>array($dynaformid,$enterpriseno)
        );
        $item = $form->getOne($opt);
        if($item === false || $item->status != $normal){
            $json['returncode'] = self::NO_RECODE_CODE;
        }else{
            $json['returncode'] = self::SUCCESS_CODE;
        }
        return $json;
    }
    /*
     * 申请审批 获取一群人的用户名
     */

    private function find_users_name(&$username, $userno, $enterpriseno) {
        $opt = array(
            'select' => 'userno,username',
            'where' => "userno IN (" . $userno . ")",
            'asArray' => true,
        );
        $user = Exterprise::findUserEmployee($opt, $enterpriseno);
        foreach ($user as $v) {
            $sp = strlen($username) ? ',' : '';
            $username .= $sp . $v['username'];
        }
    }

    /*
     * 获取我的活动列表  1判断权限 2 判断type 3去列表
     * @param $type 0为我的活动 1为全部活动
     */

    public function get_myactivity_list($userno, $enterpriseno, $pageindex, $pagesize, $type, $lasttime) {
        Doo::loadClass("Activity");
        Doo::loadClass("Enum");
        Doo::loadClass('Permission');
        Doo::loadModel('EnterpriseActivity');

        $active = new EnterpriseActivity();
        $normal = Enum::getStatusType('Normal');
        $isadmin = Permission::checkUserPermission(ModuleCode::$Activity, ActionCode::$Admin,$userno,$enterpriseno); //管理员
        $opt = array(
            'where' => 'enterpriseno=?',
            'param' => array($enterpriseno),
            'desc' => 'createtime'
        );
        if (!$isadmin) {
            if (empty($type)) {
                $opt['where'] .= ' AND (userno=? OR FIND_IN_SET(?,tousers) OR tousers=? OR FIND_IN_SET(?,joinusers) OR viewusers=?)';
                array_push($opt['param'], $userno, $userno, '', $userno, '');
            }
        }
        if ($lasttime != -1 && $pageindex == 1) {
            $nowtime = date('Y-m-d H:i:s');
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $param = $opt['where'];
            array_push($param, $updatetime, $nowtime);
            $tem_opt = array(
                'where' => $opt['where'] . ' AND lastupdatetime BETWEEN ? AND ?',
                'param' => $param
            );
            $count = $active->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $opt['where'] .= " AND status=$normal";
        $count = $active->count($opt);
        $page = ($pageindex - 1) * $pagesize;
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $opt['select'] = 'userno,tousers,joinusers,rejectusers,enterpriseno,activityid,title,endtime,createtime';
        $result = Activity::getActivity($opt);
        $list = array();
        foreach ($result as $value) {
            $has_join = 3;
            if ($userno != $value->userno && (strstr($value->tousers, "$userno") || empty($value->tousers))) {
                if (strstr($value->joinusers, "$userno"))
                    $has_join = 1;
                elseif (strstr($value->rejectusers, "$userno")) {
                    $has_join = 2;
                } elseif (!strstr($value->rejectusers, "$userno") && !strstr($value->joinusers, "$userno")) {
                    $has_join = 0;
                }
            } elseif ($userno != $value->userno) {
                $has_join = 4;
            }
            $username = Activity::getUserName($value->userno, $value->enterpriseno);
            $list[] = array(
                "userid" => $value->userno,
                'username' => $username,
                'activeid' => $value->activityid,
                "title" => $value->title,
                'activestate' => (time() > strtotime($value->endtime)) ? 1 : 0,
                "createtime" => $value->createtime,
                'has_join' => $has_join
            );
        }
        $nowtime = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $nowtime, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 获取活动详情
     */

    public function get_activity_detail($id, $userno, $enterpriseno) {
        Doo::loadClass('Enum');
        Doo::loadClass("Activity");
        Doo::loadClass('Comment');

        $normal = Enum::getStatusType('Normal');
        $postmode = Enum::getCommentMode('Activity');
        $value = Activity::getOneActivity(array('where' => "activityid=? AND enterpriseno=? AND status=?", 'param' => array($id, $enterpriseno, $normal)));
        if ($value === false) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $username = Activity::getUserName($value->userno, $value->enterpriseno);
        $rejectusers = array_filter(explode(',', $value->rejectusers));
        $joinusers = array_filter(explode(',', $value->joinusers));
        $quta = $value->maxcount == 0 ? "" : $value->maxcount - count($joinusers);
        $opt['where'] = 'postmode=? AND status=? AND postid=?';
        $opt['param'] = array($postmode, $normal, $id);
        $first_comment = Comment::getOneComment($opt);
        $comment_count = Comment::coutComments($opt);
        if ($first_comment != null) {
            $first_comment = $first_comment['username'] . ":" . $first_comment['content'];
        }
        if (empty($value->tousers)) {
            $value->tousers = $tousers = Activity::getEnterpriseUsers($enterpriseno);
            $tousers = explode(',', $tousers);
        } else {
            $tousers = explode(',', $value->tousers);
        }
        $prior_users = array_slice($tousers, 0, 3);
        $untoken = implode(',', array_diff($tousers, array_merge($rejectusers, $joinusers)));
        $has_join = 3;
        if ($userno != $value->userno && (strstr($value->tousers, "$userno") || empty($value->tousers))) {
            if (strstr($value->joinusers, "$userno"))
                $has_join = 1;
            elseif (strstr($value->rejectusers, "$userno")) {
                $has_join = 2;
            } elseif (!strstr($value->rejectusers, "$userno") && !strstr($value->joinusers, "$userno")) {
                $has_join = 0;
            }
        } elseif ($userno != $value->userno) {
            $has_join = 4;
        }
        $content = array(
            "title" => $value->title,
            "activityintro" => $value->description,
            "activitydetail" => $value->introduction,
            "activitytype" => $value->type,
            "endtime" => $value->endtime,
            "starttime" => $value->starttime,
            "userid" => $value->userno,
            "username" => $username,
            "readusers" => $value->tousers,
            'expenses' => $value->price,
            'contacter' => Activity::getUserName($value->contact, $value->enterpriseno),
            'phonenum' => $value->contactphone,
            'activityarea' => $value->address,
            'activestate' => (time() > strtotime($value->endtime)) ? 1 : 0,
            'is_limit' => $value->maxcount == 0 ? 0 : 1,
            'rejectusers' => $value->rejectusers,
            'joinusers' => $value->joinusers,
            'firstcomment' => $first_comment,
            'commentcount' => $comment_count,
            'untoken' => $untoken,
            'quota' => $quta,
            'has_join' => $has_join,
            'viewusers' => empty($value->viewusers) ? 1 : 0,
            'priorusers' => implode(',', $prior_users),
            "attachments" => $value->attachments,
            "createtime" => $value->createtime
        );
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $content);
        return $json;
    }

    /*
     * 加入活动
     */

    public function join_activity($userno, $enterpriseno, $id, $status, $reason) {
        Doo::loadClass("Activity");
        Doo::loadClass('SysMessage');
        Doo::loadClass('Exterprise');
        $value = Activity::getOneActivity(array('where' => "activityid=$id"));
        $userinfo = Exterprise::getUserInfo(array('where' => 'userno=? AND enterpriseno=?', 'param' => array($userno, $enterpriseno)));
        if ($status) {
            Activity::join($value, $userno);
            if ($userinfo != null) {
                SysMessage::send_sysMessage($id, 'activity', 63, $userno, $userinfo->username, $userinfo->enterpriseno, '', $value->userno);
            }
        } else {
            $extra_parameter = array();
            Activity::refusal($value, $userno);
            if ($reason == '') {
                $dotype = 64;
                $extra_parameter['reason'] = '';
            } else {
                $dotype = 65;
                $extra_parameter['reason'] = $reason;
            }
            //发送系统消息
            if ($userinfo != null) {
                SysMessage::send_sysMessage($id, 'activity', $dotype, $userno, $userinfo->username, $userinfo->enterpriseno, '', $value->userno, $extra_parameter);
            }
        }
        $json = array('returncode'=>self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 活动 编辑活动详情
     * @param $tousers 活动受众 全部人是为空
     */

    public function edit_activity($activityid, $userno, $enterpriseno, $viewusers, $tousers, $type, $title, $starttime, $endtime, $address, $introduction, $attachments, $contactphone, $maxcount, $price, $cost, $endjoin, $description) {
        Doo::loadClass('Activity');
        Doo::loadClass('Exterprise');
        Doo::loadClass('SysMessage');

        $userinfo = Exterprise::getUserInfo(array('where' => 'userno=? AND enterpriseno=?', 'param' => array($userno, $enterpriseno)));
        $values = array(
            'type' => $type,
            'title' => $title,
            'posters' => 'funny',
            'starttime' => $starttime,
            'endtime' => $endtime,
            'address' => $address,
            'introduction' => $introduction,
            'attachments' => $attachments,
            'contact' => $userno,
            'contactphone' => $contactphone,
            'maxcount' => $maxcount,
            'price' => $price,
            'cost' => $cost,
            'endjoin' => $endjoin,
            'tousers' => $tousers,
            'description' => $description,
            'viewusers' => empty($viewusers) ? 1 : 0,
            'status' => 1101,
            'lastupdatetime' => date(DATE_ISO8601),
            'userno' => $userno,
            'enterpriseno' => $enterpriseno
        );
        if (empty($activityid)) {
            $values['createtime'] = date(DATE_ISO8601);
            $sys_do_id = 57;
            $id = Activity::addActivity($values);
            $opt['where'] = 'activityid=? AND enterpriseno=?';
            $opt['param'] = array($id, $enterpriseno);
            $activity = Activity::getOneActivity($opt);
            //加入今天
            Doo::loadClass('Today');
            Today::add_activity($activity);
        } else {
            $sys_do_id = 58;
            $values['lastupdatetime'] = date(DATE_ISO8601);
            $values['activityid'] = $activityid;
            $id = Activity::updateActivity($values);
        }
        if ($id > 0) {
            //发送系统消息
            $touser = empty($tousers) ? 0 : $tousers;
            if ($userinfo != null) {
                SysMessage::send_sysMessage($id, 'activity', 57, $userno,'', $enterpriseno,'',$touser);
                //添加系统日志
                Activity::addSysLog($id, 77, $userno, $userinfo->username, $enterpriseno);
            }
            $json = array('returncode' => self::SUCCESS_CODE);
        } else {
            $json = array('returncode' => self::ERROR_CODE);
        }
        return $json;
    }

    /*
     * 活动  删除活动
     */

    public function del_activity($userno, $enterpriseno, $activityid) {
        Doo::loadClass('Enum');
        Doo::loadClass('Activity');
        Doo::loadClass('Exterprise');
        $normal = Enum::getStatusType('Normal');
        $opt_get = array(
            'where' => 'enterpriseno=? AND activityid=? AND status=?',
            'param' => array($enterpriseno, $activityid, $normal)
        );
        $info = Activity::getOneActivity($opt_get);
        if ($info == null) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $opt = array(
            'where' => 'enterpriseno=? AND activityid=?',
            'param' => array($enterpriseno, $activityid)
        );
        Activity::deleteActivity($opt);
        Activity::addSysLog($activityid, 79, $userno, '', $enterpriseno);
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 文件柜  获取快捷方式列表  首页内容包括个人文件柜 公司文件柜 快捷方式
     */

    public function get_shortcut_list($userno, $enterpriseno) {
        Doo::loadClass('Doc');
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        $sysdirectory = array('公司文件柜', '个人文件柜');
        $opt = array(
            'where' => 'userno=? AND enterpriseno=? AND status=?',
            'param' => array($userno, $enterpriseno, $normal),
        );
        $res = Doc::getShortcuts($opt);
        $list = array();
        foreach ($res as $v) {
            $doc = Doc::getOneDocuments(array('where' => 'documentid=' . $v->documentid));
            $list[] = array(
                'shortcutid' => $v->id,
                'documentid' => $v->documentid,
                'size' => $doc->filesize,
                'name' => $v->name,
                'filetype' => $v->isfile ? 0 : 1,
                'modifydate' => $doc->lastupdatedtime,
                'realpath' => $v->fullpath,
                'filepath' => $doc->filepath,
                'userno' => $doc->userno,
                'type' => $doc->type,
                'isshare' => $doc->isshare,
            );
        }
        foreach ($sysdirectory as $value) {
            $opt_dir = array('where' => 'directoryname=? AND enterpriseno=?', 'param' => array($value, $enterpriseno));
            $info = Doc::getOneDocuments($opt_dir);
            $list[] = array(
                'documentid' => $info->documentid,
                'size' => $info->filesize,
                'name' => $info->directoryname,
                'type' => 3,
                'modifydate' => $info->lastupdatedtime,
                'realpath' => $info->fullpath,
                'filepath' => $info->filepath
            );
        }
        $json = array('returncode' => self::SUCCESS_CODE, 'contents' => $list);
        return $json;
    }

    /*
     * 文件柜  获取目录底下文件列表
     * 文件列表为先文件夹后文件并按创建时间排序
     */

    public function get_dirfile($userno, $enterpriseno, $dir) {
        Doo::loadClass('Enum');
        Doo::loadClass('Doc');
        $normal = Enum::getStatusType('Normal');
        $list = array();
        $tempdirlist = array(); //文件夹
        $templist = array(); //文件
        switch ($dir) {
            case 'companyfile':
                $opt = array('where' => 'enterpriseno=? AND directoryname=?', 'param' => array($enterpriseno, '公司文件柜'));
                $res = Doc::getOneDocuments($opt);
                $model = Doc::getDocuments(array('where' => 'parentid=? AND status=? AND isfile=0',
                            'param' => array($res->documentid, $normal),
                            'asArray' => true, 'desc' => 'createtime'));
                $tempdirlist = Doc::isDisplayDoc($model, $userno, $enterpriseno, $type = 0);

                $modelone = Doc::getDocuments(array('where' => 'parentid=? AND status=? AND isfile=1',
                            'param' => array($res->documentid, $normal),
                            'asArray' => true, 'desc' => 'createtime'));
                $templist = Doc::isDisplayDoc($modelone, $userno, $enterpriseno, $type = 0);
                break;
            case 'personalfile':
                $optone = array('where' => 'enterpriseno=? AND directoryname=?', 'param' => array($enterpriseno, '个人文件柜'));
                $res = Doc::getOneDocuments($optone);
                $opttwo = array('where' => 'userno=? AND parentid=? AND status=? AND isfile=0', 'param' => array($userno, $res->documentid, $normal),
                    'asArray' => true, 'desc' => 'createtime'
                );
                $model = Doc::getDocuments($opttwo);
                $tempdirlist = Doc::isDisplayDoc($model, $userno, $enterpriseno, $type = 1);
                $opt = array('where' => 'userno=? AND parentid=? AND status=? AND isfile=1', 'param' => array($userno, $res->documentid, $normal),
                    'asArray' => true, 'desc' => 'createtime'
                );
                $modelone = Doc::getDocuments($opt);
                $templist = Doc::isDisplayDoc($modelone, $userno, $enterpriseno, $type = 1);
                break;
            case 'colleagueshare':
                $option['where'] = 'userno!=? AND status=? AND isshare=1 AND isfile=0 AND enterpriseno=? AND (FIND_IN_SET(' . $userno . ',sharepermissions) OR sharepermissions="")';
                $option['param'] = array($userno, 1101, $enterpriseno);
                $option['desc'] = 'createtime';
                $option['asArray'] = true;
                $tempdirlist = Doc::getDocuments($option);
                $option['where'] = 'userno!=? AND status=? AND isshare=1 AND isfile=1 AND enterpriseno=? AND (FIND_IN_SET(' . $userno . ',sharepermissions) OR sharepermissions="")';
                $templist = Doc::getDocuments($option);
                break;
            case 'myshare':
                $option['where'] = 'userno=? AND status=? AND isshare=1 AND enterpriseno=? AND isfile=0';
                $option['param'] = array($userno, 1101, $enterpriseno);
                $option['desc'] = 'createtime';
                $option['asArray'] = true;
                $tempdirlist = Doc::getDocuments($option);
                $option['where'] = 'userno=? AND status=? AND isshare=1 AND enterpriseno=? AND isfile=1';
                $templist = Doc::getDocuments($option);
                break;
            default :
                $option['where'] = '  parentid=? AND enterpriseno=? AND status =? AND isfile=0';
                $option['param'] = array($dir, $enterpriseno, 1101);
                $option['desc'] = 'createtime';
                $option['asArray'] = true;
                $tempdirlist = Doc::getDocuments($option);
                $option['where'] = '  parentid=? AND enterpriseno=? AND status =? AND isfile=1';
                $templist = Doc::getDocuments($option);
        }
        $a = empty($tempdirlist) ? 0 : 1;
        $b = empty($templist) ? 0 : 2;
        $c = $a + $b;
        switch ($c) {
            case 0:
                $list = array();
                break;
            case 1:
                $list = $tempdirlist;
                break;
            case 2:
                $list = $templist;
                break;
            case 3:
                $list = array_merge($tempdirlist, $templist);
                break;
        }
        $contents = array();
        foreach ($list as $v) {
            $contents[] = array(
                'documentid' => $v['documentid'],
                'isshare' => $v['isshare'],
                'size' => $v['filesize'],
                'name' => $v['isfile'] ? $v['filename'] : $v['directoryname'],
                'parentid' => $v['parentid'],
                'type' => $v['isfile'],
                'modifydate' => $v['lastupdatedtime'],
                'realpath' => $v['filepath']
            );
        }
        $json = array('returncode' => self::SUCCESS_CODE, 'contents' => $contents);
        return $json;
    }

    /*
     * 文件柜  创建文件夹 
     * 根据各种情况判断是否能创建文件夹和是否成功，返回相关信息
     */

    public function create_directory($userno, $enterpriseno, $level, $pid, $directoryname, $type) {
        Doo::loadClass('Enum');
        Doo::loadClass('Doc');
        $normal = Enum::getStatusType('Normal');
        $sysdirectory = array('文件柜首页', '公司文件柜', '个人文件柜');
        if ($pid == 0 && $level == 1) {
            $res = Doc::createDirectory($userno, $enterpriseno, $pid, $level, $directoryname, '', 0, '', '', '', $type);
            if ($res == null) {
                $json = array('returncode' => self::CREATE_FAIL);
            } else {
                $json = array('returncode' => self::SUCCESS_CODE);
            }
        } else {
            //查找有没有该父级文件夹
            $fmodel = Doc::getOneDocuments(array('where' => 'documentid=? AND status=?', 'param' => array($pid, $normal)));
            if ($fmodel == null) {
                $json = array('returncode' => self::CREATE_FAIL);
            } else {
                if ($directoryname == '') {
                    $json = array('returncode' => self::EMPTY_DIR);
                    return $json;
                }
                //检测是否有相同的文件夹
                if (array_search($directoryname, $sysdirectory)) {
                    $json = array('returncode' => self::SYS_RENAME);
                    return $json;
                }
                //是否只读
                if (Doc::checkIsReadOnly($pid, 'create')) {
                    $json = array('returncode' => self::READ_ONLY);
                    return $json;
                }
                //权限
                if (!Doc::isCanCreate($pid, $userno, $enterpriseno)) {
                    $json = array('returncode' => self::NO_LIMIT);
                    return $json;
                }
                if ($type == 1) {
                    //如果是个人文件柜，那么当前用户的
                    $model = Doc::getOneDocuments(array('where' => 'directoryname=? AND type=' . $type . ' AND status=1101 AND enterpriseno=' . $enterpriseno . ' AND parentid=? AND userno=?', 'param' => array($directoryname, $pid, $userno)));
                }else
                    $model = Doc::getOneDocuments(array('where' => 'directoryname=? AND type=' . $type . ' AND status=1101 AND enterpriseno=' . $enterpriseno . ' AND parentid=?', 'param' => array($directoryname, $pid)));

                if ($model == null) {
                    if ($type == 0) {
                        $res = Doc::createDirectory($userno, $enterpriseno, $pid, $level, $directoryname, '', 0, 0, 0, 0);
                    } else if ($type == 1) {
                        $res = Doc::createDirectory($userno, $enterpriseno, $pid, $level, $directoryname, '', 0, $userno, $userno, $userno, $type);
                    }
                    if ($type == 0 || $type == 1) {
                        //写系统日志
                        Doc::addsyslog($res['id'], 84, $userno, $enterpriseno);
                    }
                    $json = array('returncode' => self::SUCCESS_CODE);
                } else {
                    $json = array('returncode' => self::EXISTED);
                }
            }
        }
        return $json;
    }

    /*
     * 文件柜  重命名文件或文件夹 需检查是否为空 为否重名 是否有权限 是否只读
     */

    public function rename_doc($userno, $enterpriseno, $id, $newname) {
        Doo::loadClass('Doc');
        $sysdirectory = array('文件柜首页', '公司文件柜', '个人文件柜');
        $info = Doc::getOneDocuments(array('where' => 'status=1101 AND documentid=' . $id));
        if ($info == null) {
            $json = array('returncode' => self::NO_RECODE_CODE);
        } else {
            if (Doc::isInStr($info->directoryproperty, '1')) {
                $json = array('message' => self::READ_ONLY);
                return $json;
            }
            //是否只读
            if (Doc::checkIsReadOnly($id)) {
                $json = array('returncode' => self::READ_ONLY);
                return $json;
            }
            //检测是否有相同的文件夹
            if (array_search($newname, $sysdirectory)) {
                $json = array('returncode' => self::SYS_RENAME);
                return $json;
            }
            //权限
            if (!Doc::isCanWrite($id, $userno, $enterpriseno)) {
                $json = array('returncode' => self::NO_PERMISSION_CODE);
                return $json;
            }
            if ($newname == '') {
                $json = array('returncode' => self::EMPTY_DIR);
                return $json;
            }
            $opt = array('where' => 'documentid=' . $id);
            if ($info->isfile == '1') {
                $field = 'filename';
                $original_name = $info->filename;
            } else {
                $field = 'directoryname';
                $original_name = $info->directoryname;
            }
            if ($info->type == 1) {
                $model = Doc::getOneDocuments(array('where' => $field . '=? AND status=1101 AND enterpriseno=' . $enterpriseno . ' AND parentid=? AND type=? AND userno=?', 'param' => array($newname, $info->parentid, $info->type, $userno)));
            }else
                $model = Doc::getOneDocuments(array('where' => $field . '=? AND status=1101 AND enterpriseno=' . $enterpriseno . ' AND parentid=? AND type=?', 'param' => array($newname, $info->parentid, $info->type)));
            if ($model == null) {
                $extra_data = array('original_name' => $original_name);
                $time = date(DATE_ISO8601);
                $values = array(
                    $field => $newname . $info->extension,
                    'lastupdatedtime' => $time,
                    'lastupdateduserno' => $userno
                );
                Doc::updateDocuments($values, $opt);

                //写系统日志
                if ($info->type == 1)
                    Doc::addsyslog($id, 90, $userno, $enterpriseno, $extra_data);
                else
                    Doc::addsyslog($id, 89, $userno, $enterpriseno, $extra_data);

                $json = array('returncode' => self::SUCCESS_CODE);
            } else {
                $json = array('returncode' => self::EXISTED);
            }
        }
        return $json;
    }

    /*
     * 文件柜 删除文件夹或者文件 如果是删除文件夹，需要同时删除文件夹底下的文件
     */

    public function delete_doc($id, $userno, $enterpriseno) {
        Doo::loadClass('Enum');
        Doo::loadClass('Doc');
        $normal = Enum::getStatusType('Normal');
        $delete = Enum::getStatusType('Delete');
        $info = Doc::getDocuments(array('where' => 'status=' . $normal . ' AND documentid IN (' . $id . ')', 'asArray' => true));
        if ($info == null) {
            $json = array('returncode' => self::NO_RECODE_CODE);
        } else {
            //是否只读
            if (Doc::checkIsReadOnly($info[0]['documentid'])) {
                $json = array('returncode' => self::READ_ONLY);
                return $json;
            }
            //权限
            if (!Doc::isCanWrite($info[0]['documentid'], $userno, $enterpriseno)) {
                $json = array('returncode' => self::NO_PERMISSION_CODE);
                return $json;
            }
            $opt = array('where' => 'documentid IN (' . $id . ') AND status=' . $normal);
            $values = array('status' => $delete);
            Doc::updateDocuments($values, $opt);
            //删除文件夹下面的所有文件夹和文件
            if (!strpos($id, ',')) {
                //删除单个文件
                $opt_ = array('where' => 'FIND_IN_SET(' . $id . ',fullpath) AND status=' . $normal);
                Doc::updateDocuments(array('status' => $delete), $opt_);
                //写系统日志
                $onedocinfo = Doc::getOneDocuments(array('where' => 'documentid=' . $id));
                if ($onedocinfo != null) {
                    if ($onedocinfo->isfile == 1)
                        Doc::addsyslog($id, 88, $userno, $enterpriseno);
                    else
                        Doc::addsyslog($id, 83, $userno, $enterpriseno);
                }
            } else {
                //删除多个文件
                $id_array = explode(',', $id);
                foreach ($id_array as $v) {
                    $opt_['where'] = 'FIND_IN_SET(' . $v . ',fullpath) AND status=' . $normal;
                    Doc::updateDocuments(array('status' => $delete), $opt_);
                    //写系统日志
                    $deletedocs = Doc::getDocuments($opt_);
                    if ($deletedocs != '') {
                        foreach ($deletedocs as $v) {
                            if ($v->isfile == 1)
                                Doc::addsyslog($v->documentid, 88, $userno, $enterpriseno);
                            else
                                Doc::addsyslog($v->documentid, 83, $userno, $enterpriseno);
                        }
                    }
                }
            }
            $json = array('returncode' => self::SUCCESS_CODE);
        }
        return $json;
    }

    /*
     * 文件柜  共享文件 
     * @param $isshare 是否共享
     * @param $share  共享的权限
     */

    public function share_doc($id, $isshare, $share, $enterpriseno) {
        Doo::loadClass('Doc');
        if (!strpos($id, ',')) {
            $info = Doc::getOneDocuments(array('where' => 'documentid=? AND enterpriseno=?', 'param' => array($id, $enterpriseno)));
            if ($info == null) {
                $json = array('returncode' => self::NO_RECODE_CODE);
                return $json;
            } else {
                $values = array('isshare' => $isshare, 'sharepermissions' => $share);
                Doc::updateDocuments($values, array('where' => 'documentid=' . $id));
            }
        } else {
            $id_array = explode(',', $id);
            foreach ($id_array as $value) {
                $values = array('isshare' => $isshare, 'sharepermissions' => $share);
                Doc::updateDocuments($values, array('where' => 'documentid=' . $value));
            }
        }
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 文件柜  取消共享 1、先判断文件是否已共享 2 判断是否是文件创建者
     * @param $did  documentid
     */

    public function cancle_share_doc($did, $userno) {
        Doo::loadClass('Enum');
        Doo::loadClass('Doc');
        $normal = Enum::getStatusType('Normal');
        $opt = array('where' => 'status=? AND type=1 AND documentid=? AND isshare=1 AND userno=?', 'param' => array($normal, $did, $userno));
        $info = Doc::getOneDocuments($opt);

        if ($info == null) {
            $json = array('returncode' => self::NOT_SHARE);
        } else {
            if ($userno == $info->userno) {
                $info->isshare = 0;
                $info->sharepermissions = '';
                $info->update();
                $json = array('returncode' => self::SUCCESS_CODE);
            } else {
                $json = array('returncode' => self::NO_PERMISSION_CODE);
            }
        }
        return $json;
    }

    /*
     * 文件柜  获取共享文件的共享人员 
     * @param $id  documentid
     */

    public function get_doc_shareuser($id, $enterpriseno) {
        Doo::loadClass('Enum');
        Doo::loadClass('Doc');
        $normal = Enum::getStatusType('Normal');
        $opt = array(
            'where' => 'documentid=? AND status=? AND enterpriseno=?',
            'param' => array($id, $normal, $enterpriseno)
        );
        $model = Doc::getOneDocuments($opt);
        if ($model != null) {
            $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array('isshare' => $model->isshare, 'sharepermissions' => $model->sharepermissions));
        } else {
            $json = array('returncode' => self::NO_RECODE_CODE);
        }
        return $json;
    }

    /*
     * 文件柜  上传文件到文件柜
     * @param $id  上传的文件的附件id attachmentid
     * @param $pid 上级目录documentid
     * @param level 文件所在层级 。。。。。
     * @param $filetype 文件或文件夹属性 0为公司 1为个人
     * @param $oldname 旧文件名
     * @param $path 文件路径
     * @param $size 文件大小
     * @param $extension 文件扩展名
     * @param $filetype 文件类型
     */

    public function add_doc($userno, $enterpriseno, $id, $pid, $level, $oldname, $path, $size, $extension, $filetype) {
        Doo::loadClass('Enum');
        Doo::loadClass('Doc');
        $normal = Enum::getStatusType('Normal');
        $time = date(DATE_ISO8601);
        $pdocinfo = Doc::getOneDocuments(array('where' => 'documentid=' . $pid . ' AND status=' . $normal));
        if ($pdocinfo == null) {
            $fullpath = '';
            $canviewusers = '';
            $canopusers = '';
            $canadminusers = '';
        } else {
            if ($pdocinfo->fullpath == '') {
                $fullpath = $pid;
            } else {
                $fullpath = $pdocinfo->fullpath . ',' . $pid;
            }
            $canviewusers = $pdocinfo->canviewusers;
            $canopusers = $pdocinfo->canoperatusers;
            $canadminusers = $pdocinfo->manageruserno;
        }
        $values = array(
            'parentid' => $pid,
            'level' => $level,
            'enterpriseno' => $enterpriseno,
            'isfile' => '1',
            'filepath' => $path,
            'userno' => $userno,
            'createtime' => $time,
            'lastupdatedtime' => $time,
            'lastupdateduserno' => $userno,
            'status' => $normal,
            'filesize' => $size,
            'extension' => $extension,
            'filename' => $oldname,
            'fullpath' => $fullpath,
            'type' => $filetype
        );
        $no = Doc::insertDocuments($values);
        if ($no > 0) {
            Doo::loadClass('Attachment');
            Attachment::set_attachment_model($id, Enum::get_attachment_modeltype("Doc"), $no);
            //转换文件
            if (!Doc::doc_unConversion($extension)) {
                Doc::fileConversion($path, $extension, 'mobapi');
            }
            //写系统日志
            Doc::addsyslog($no, 86, $userno, $enterpriseno);
            $json = array('returncode' => self::SUCCESS_CODE);
        } else {
            $json = array('returncode' => self::ERROR_CODE);
        }
        return $json;
    }

    /*
     * 文件柜 设置目标文件夹或文件属性
     * @param $id 目标文件或文件夹的 documentid
     * @param $property 属性值
     * @param $userno
     */

    public function save_docproperty($id, $userno, $enterpriseno, $property) {
        Doo::loadClass('Doc');
        $info = Doc::getOneDocuments(array('where' => 'documentid=' . $id));
        if ($info == null) {
            $json = array('returncode' => self::NO_RECODE_CODE);
        } else {
            $values = array('directoryproperty' => $property);
            Doc::updateDocuments($values, array('where' => 'documentid=' . $id));
            if ($info->directoryproperty != $property) {
                Doc::addsyslog($id, $property, $userno, $enterpriseno);
            }
            $json = array('returncode' => self::SUCCESS_CODE);
        }
        return $json;
    }

    /*
     * 文件柜  获取同事共享文件列表  列表结果为 先分每个共享的人 再按文件夹 文件排序
     */

    public function get_colleague_sharelist($userno, $enterpriseno) {
        Doo::loadClass('Doc');
        $list = array();
        $tempdirlist = array(); //文件夹
        $templist = array();
        $data = array();
        $tem = array();
        $ustr = '';              //分享同事的userno
        $opt = array(
            'where' => 'userno!=? AND status=? AND isshare=1 AND enterpriseno=? AND (FIND_IN_SET(' . $userno . ',sharepermissions) OR sharepermissions="")',
            'param' => array($userno, 1101, $enterpriseno),
            'select' => 'userno',
            'asArray' => true,
        );
        $shareuserno = Doc::getDocuments($opt);
        if (!empty($shareuserno)) {
            foreach ($shareuserno as $value) {
                if(strpos($ustr,$value['userno']) === false){
                    if(!empty($ustr)){
                        $ustr .= ','.$value['userno'];
                    }else{
                        $ustr = $value['userno'];
                    }
                }
                $option = array(
                    'where' => 'userno=? AND status=? AND isshare=1 AND isfile=0 AND enterpriseno=? AND (FIND_IN_SET(' . $userno . ',sharepermissions) OR sharepermissions="")',
                    'param' => array($value['userno'], 1101, $enterpriseno),
                    'desc' => 'createtime',
                    'asArray' => true
                );
                $tempdirlist = Doc::getDocuments($option);
                $option['where'] = 'userno=? AND status=? AND isshare=1 AND isfile=1 AND enterpriseno=? AND (FIND_IN_SET(' . $userno . ',sharepermissions) OR sharepermissions="")';
                $templist = Doc::getDocuments($option);
                $a = empty($tempdirlist) ? 0 : 1;
                $b = empty($templist) ? 0 : 2;
                $c = $a + $b;
                switch ($c) {
                    case 0:
                        $list = array();
                        break;
                    case 1:
                        $list = $tempdirlist;
                        break;
                    case 2:
                        $list = $templist;
                        break;
                    case 3:
                        $list = array_merge($tempdirlist, $templist);
                        break;
                }
                $tem = '';
                foreach ($list as $v) {
                    $tem[] = array('documentid' => $v['documentid'],
                        'isshare' => $v['isshare'],
                        'size' => $v['filesize'],
                        'name' => $v['isfile'] ? $v['filename'] : $v['directoryname'],
                        'parentid' => $v['parentid'],
                        'type' => $v['isfile'],
                        'modifydate' => $v['lastupdatedtime'],
                        'realpath' => $v['filepath']);
                }
                $data[$value['userno']] = $tem;
            }
        }
        $json = array('returncode' => self::SUCCESS_CODE, 'contents' => $data,'colleagues'=>$ustr);
        return $json;
    }

    /*
     * 文件柜  获取文件属性 
     */

    public function get_doc_property($enterpriseno, $id) {
        Doo::loadClass('Doc');
        $info = Doc::getOneDocuments(array('where' => 'documentid=? AND enterpriseno=?', 'param' => array($id, $enterpriseno)));
        if ($info == null) {
            $json = array('returncode' => self::NO_RECODE_CODE);
        } else {
            $content = array('directoryproperty' => $info->directoryproperty);
            $json = array('returncode' => self::SUCCESS_CODE, 'content' => $content);
        }
        return $json;
    }

    /*
     * 文件柜  修改快捷方式
     * @param $id  快捷方式id
     * @param $type 动作 删除或更新
     * @param $value 新的快捷方式名字
     */

    public function edit_shortcut($enterpriseno, $id, $type, $value) {
        Doo::loadClass('Enum');
        Doo::loadClass('Doc');
        $normal = Enum::getStatusType('Normal');
        $delete = Enum::getStatusType('Delete');

        $opt['where'] = 'id=? AND status=? AND enterpriseno=?';
        $opt['param'] = array($id, $normal, $enterpriseno);
        $info = Doc::getOneShortcuts($opt);
        if ($info == null) {
            $json = array('returncode' => self::NO_RECODE_CODE);
        } else {
            if ($type == 'delete') {
                $info->status = $delete;
                $info->update();
            } else if ($type == 'updatename') {
                $info->name = $value;
                $info->update();
            }
            $json = array('returncode' => self::SUCCESS_CODE);
        }
        return $json;
    }

    /*
     * 客户  获取我的客户或者公司客户列表
     * @param $type  查看列表类型 默认为0 我的客户 1 公司客户
     * 客户的删除时真删delete,并将附近一条记录的updatetime修改，所以检测update的时候要放开条件去检测本公司的
     */

    public function get_customer_list($userno, $enterpriseno, $updatetime, $pageindex, $pagesize, $type) {
        Doo::loadClass('Customer');
        $opt = array('company' => $type);
        if ($updatetime != -1 && $pageindex == 1) {
            $lastupdatetime = date('Y-m-d H:i:s', $updatetime);
            $opt_tem = array('lastupdatetime' => $lastupdatetime, 'update' => 1);
            $updatecount = Customer::get_customer_list($opt_tem, 'count', $enterpriseno, $userno);
            if ($updatecount <= 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = Customer::get_customer_list($opt, 'count', $enterpriseno, $userno);
        $page = ($pageindex - 1) * $pagesize;
        $opt['limit'] = ' limit ' . ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $res = Customer::get_customer_list($opt, 'listForMobile', $enterpriseno, $userno);
        $list = array();
        $contactor = Doo::loadModel('EnterpriseCustomerContactor', true);
        foreach ($res as $v) {
            $v = (object) $v;
            $arr = array(
                "archivesid" => $v->profileid, 
                "principal" => $v->manager, 
                "status" => $v->type, 
                "companyname" => $v->shortname, 
                'contacts' => array());
            $contactor_res = $contactor->find(array('select' => 'contactorid,name', 'where' => 'FIND_IN_SET(contactorid,?)', 'param' => array($v->contactor)));
            foreach ($contactor_res as $c) {
                $arr['contacts'][] = array('id' => $c->contactorid, 'name' => $c->name);
            }
            $list[] = $arr;
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 客户  获取客户详情
     * 客户模块分四个表 CustomerProfiles主表  CustomerProfilesInfo信息表  CustomerContactor详细信息表  CustomerAddress存放地址表
     */

    public function get_customer_detail($userno, $id) {
        $m = Doo::loadModel('EnterpriseCustomerProfiles', true);
        $rmodel = Doo::loadModel('EnterpriseCustomerProfilesInfo', true);
        $contactor = Doo::loadModel('EnterpriseCustomerContactor', true);
        $address = Doo::loadModel('EnterpriseCustomerAddress', true);
        $m->profileid = $id;
        $rmodel->profileid = $id;
        $rm = $rmodel->getOne();
        $res = $m->getOne();
        if (!$res || !$rm)  
            return array('returncode' => self::NO_RECODE_CODE);
        foreach ($rm as $k => $v) {
            $res->{$k} = $v;
        }
        $contactor_res = $contactor->find(array('where' => 'FIND_IN_SET(contactorid,?)', 'param' => array($res->contactor)));
        $address_res = $address->getOne(array('where' => 'FIND_IN_SET(addressid,?)', 'param' => array($res->address,$res->address), 'custom' => 'ORDER BY FIND_IN_SET(addressid,?)'));
        $arr['archivesid'] = $id;
        $arr['status'] = $res->type;
        $arr['shortname'] = $res->shortname;
        $arr['fullname'] = $res->fullname;
        $arr['category'] = $res->custype;
        $arr['level'] = $res->grade;
        $arr['stage'] = $res->state;
        $arr['industryname'] = $res->industryname;
        $arr['area'] = $res->districtname;
        $arr['cooperationTime'] = $res->startdate;
        $arr['phone'] = $res->phone;
        $arr['fax'] = $res->fax;
        $arr['enterpriseno'] = $res->oanum;
        $arr['businesser'] = $res->businesser;
        $arr['mark'] = $res->mark;
        $arr['manager'] = $res->manager;
        $arr['attachment'] = $res->license;
        $arr['address'] = $address_res ? sprintf("%s-%s-%s %s", $address_res->provincename, $address_res->cityname, $address_res->districtname, $address_res->streetname) : '';
        foreach ($contactor_res as $c) {
            $userno = json_decode($c->oakey);
            $oanumber = is_object($userno) ? $userno->value : '';
            $skype = json_decode($c->skype);
            $skypeno = is_object($skype) ? $skype->value : '';
            $wangwang = json_decode($c->wangwang);
            $wangwangno = is_object($wangwang) ? $wangwang->value : '';
            $weibo = json_decode($c->weibo);
            $weibono = is_object($weibo) ? $weibo->value : '';
            $qq = json_decode($c->qq);
            $qqno = is_object($qq) ? $qq->value : '';
            $msn = json_decode($c->MSN);
            $msnno = is_object($msn) ? $msn->value : '';
            $birthday = json_decode($c->birthday);
            $birthdaytime = is_object($birthday) ? $birthday->value : '';
            $address_res = $address->getOne(array('where' => 'FIND_IN_SET(addressid,?)', 'param' => array($c->address,$c->address), 'custom' => 'ORDER BY FIND_IN_SET(addressid,?)'));
            $cusaddress = $address_res ? sprintf("%s-%s-%s %s", $address_res->provincename, $address_res->cityname, $address_res->districtname, $address_res->streetname) : '';
            
            $arr['contacts'][] = array(
                "oanumber" => $oanumber, 
                "position" => $c->position, 
                "mobilephone" => $c->mobile, 
                "telphone" => $c->phone, 
                "email" => $c->email, 
                "fax" => $c->fax, 
                "qq" => $qqno, 
                "msn" => $msnno, 
                "skype" => $skypeno, 
                "ali" => $wangwangno, 
                "sinaweibo" => $weibono, 
                "birthday" => $birthdaytime, 
                "address" => $cusaddress, 
                "remark" => $c->remark, 
                'name' => $c->name
            );
        }
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $arr);
        return $json;
    }

    /*
     * 客户  添加客户档案
     */

    public function edit_customer($userno, $enterpriseno, $type, $shortname, $custype, $fax, $phone, $manager, $license, $proviceid, $provice, $cityid, $city, $districtid, $district, $street, $contacts) {
        Doo::loadClass('Common');
        $m = Doo::loadModel('EnterpriseCustomerProfiles', true);
        $m->type = $type;
        $m->userno = $userno;
        $m->enterpriseno = $enterpriseno;

        $m->shortname = $shortname;
        $m->custype = $custype;
        $m->fax = $fax;
        $m->status = 1101;
        $nameindex = Common::Pinyin($shortname, 'utf-8');
        $m->nameindex = $nameindex;
        $m->addmode = 'auto';
        $m->createtime = date("Y-m-d H:i:s");
        $m->updatetime = date('Y-m-d H:i:s');
        $m->phone = $phone;
        $m->manager = $manager;
        $rm = Doo::loadModel('EnterpriseCustomerProfilesInfo', true);
        $rm->license = $license;
        $rm->status = 1101;
        $rm->fullname = $shortname;
        $rm->enterpriseno = $enterpriseno;
        $rm->createtime = date(DATE_ISO8601);
        $rm->updatetime = date(DATE_ISO8601);
        $cid = array();
        $address = Doo::loadModel('EnterpriseCustomerAddress', true);
        $address->provinceid = $proviceid;
        $address->provincename = $provice;
        $address->cityid = $cityid;
        $address->cityname = $city;
        $address->districtid = $districtid;
        $address->districtname = $district;
        $address->streetname = $street;
        $address->enterpriseno = $enterpriseno;
        $address->createtime = date(DATE_ISO8601);
        $address->updatetime = date(DATE_ISO8601);
        $aid = $address->insert();
        if ($contacts) {
            $contacts = json_decode(stripslashes($contacts));
            if (is_object($contacts) || is_array($contacts)) {
                foreach ($contacts as $v) {
                    $contactor = Doo::loadModel('EnterpriseCustomerContactor', true);
                    $contactor->name = isset($v->name) ? $v->name : '';
                    $contactor->mobile = isset($v->phone) ? $v->phone : '';
                    $contactor->email = isset($v->email) ? $v->email : '';
                    $contactor->enterpriseno = $enterpriseno;
                    $contactor->createtime = date('Y-m-d H:i:s');
                    $contactor->updatetime = date('Y-m-d H:i:s');
                    try {
                        $id = $contactor->insert();
                        $cid[] = $id;
                    } catch (Exception $e) {
                        $msg = $e->getMessage();
                        $json = array('returncode' => self::ERROR_CODE, 'message' => $msg);
                        return $json;
                    }
                }
            }
        }
        try {
            $m->contactor = implode(',', $cid);
            $m->maincontactor = $cid[0];
            $m->address = $aid;
            $opt['where'] = 'enterpriseno=? AND addmode=?';
            $opt['param'] = array($m->enterpriseno, 'auto');
            $opt['desc'] = 'customernum';
            $opt['where'] = 'LENGTH(customernum)=7';
            $res = Doo::db()->getOne('EnterpriseCustomerProfiles', $opt);
            if ($res) {
                $m->customernum = sprintf("C%06d", (substr($res->customernum, 1, 6) + 1));
            } else {
                $m->customernum = 'C000000';
            }
            $mid = $m->insert();
            $rm->profileid = $mid;
            $rm->insert();
            $json = array('returncode' => self::SUCCESS_CODE);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            $json = array('returncode' => self::ERROR_CODE);
        }
        return $json;
    }

    /*
     * 服务拜访  获取我的拜访列表
     *     //     visited 当前访问或者服务的ID
      //status 0为草稿 1为已发布
      //themename 访问或服务的主题名称
      //createtime创建时间
      //visitno 0为全部阅读 其他数字表示未阅读用户数
      //typeid 0为拜访 1为服务
      //Tips: 排列规则，草稿文件按时间逆序在列表最前面显示，跟着按时间逆序排列服务/拜访
     * 我的拜访列表 ： userno为当前用户 and 用户在 visituser（服务/拜访人员） and enterpriseno为当前公司号 and 状态为正常
     */

    public function get_myvisit_list($userno, $enterpriseno, $pageindex, $pagesize, $updatetime) {
        Doo::loadClass('Vist');
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        //创建者且是服务拜访人员之一
        $opt = array(
            'where' => 'userno=? AND (FIND_IN_SET(?,visituser)) AND enterpriseno=?',
            'param' => array($userno, $userno, $enterpriseno)
        );
        if ($updatetime != -1 && $pageindex == 1) {
            $updatetime = date('Y-m-d H:i:s', $updatetime);
            $updateopt = $opt;
            $updateopt['where'] .= " AND updatetime >='{$updatetime}'";
            $count = Vist::countVisit($updateopt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $opt['where'] .= " AND status={$normal}";
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $opt['desc'] = 'createtime';
        $list = array();
        $res = Vist::getVisit($opt);
        $count = Vist::countVisit($opt);
        foreach ($res as $v) {
            $tousers_count = $v->tousers ? substr_count(trim($v->tousers, ','), ',') + 1 : 0;
            $readusers_count = $v->markinguser ? substr_count(trim($v->markinguser, ','), ',') + 1 : 0;
            $list[] = array(
                'visitid' => $v->visitid,
                'status' => $v->status,
                'themename' => $v->profileidname,
                'createtime' => $v->createtime,
                'visitno' => $tousers_count - $readusers_count,
                'typeid' => $v->modeltype
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 服务拜访   服务拜访我的批阅
     * 
     */

    public function get_myremark_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass('Enum');
        Doo::loadClass('Vist');
        $normal = Enum::getStatusType('Normal');
        $where = 'FIND_IN_SET(' . $userno . ',tousers) AND enterpriseno = ' . $enterpriseno;
        if ($lasttime == -1 && $pageindex == 1) {
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $tem_opt['where'] = $where . " AND updatetime>='{$updatetime}'";
            $count = Vist::countVisit($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $opt['where'] = $where." AND status=$normal";
        $opt['desc'] = 'createtime';
        $list = array();
        $res = Vist::getVisit($opt);
        $count = Vist::countVisit($opt);
        foreach ($res as $v) {
            $list[] = array(
                'visitid' => $v->visitid,
                'status' => substr_count($v->markinguser, $userno), //已阅或未阅
                'themename' => $v->profileidname,
                'createtime' => $v->createtime,
                'visituser' => $v->userno,
                'count' => $count,
                'typeid' => $v->modeltype
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 服务拜访  拜访中同事记录列表
     * 有管理权限：创建者不是自己，并且不是创建者
     * 无管理权限：有查看客户权限限制
     */

    public function get_otherrecorder_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass('Customer');
        Doo::loadClass('Permission');
        Doo::loadClass('Vist');
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        $canViewProfileid = Customer::get_customer_list(array('type' => 'all', 'company' => true), 'id', $enterpriseno, $userno);
        $profileidlist = '';
        if (count($canViewProfileid) > 0) {
            foreach ($canViewProfileid as $var) {
                $profileidlist = $var['profileidlist'];
            }
        }
        $opt = array(
            'where'=>'!(FIND_IN_SET(?,visituser) AND userno=?) AND enterpriseno=?',
            'param'=>array($userno,$userno,$enterpriseno)
        );
        $isadmin = Permission::checkUserPermission(ModuleCode::$Visit, ActionCode::$Admin,$userno,$enterpriseno); //管理所有
        if (!$isadmin) {
            $opt['where'] .= ' AND FIND_IN_SET(profileid,?)';
            $opt['param'] = array($userno,$userno,$enterpriseno,$profileidlist);
        }
        if ($lasttime != -1 && $pageindex == 1) {
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $tem_opt = $opt;
            $tem_opt['where'] .= " AND updatetime>'{$updatetime}'";
            $count = Vist::countVisit($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $opt['where'] .= " AND status={$normal}";
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $opt['desc'] = 'createtime';
        $list = array();
        $res = Vist::getVisit($opt);
        $count = Vist::countVisit($opt);

        foreach ($res as $v) {
            $list[] = array(
                'visitid' => $v->visitid,
                'status' => substr_count($v->markinguser, $userno), //已阅或未阅
                'themename' => $v->profileidname,
                'createtime' => $v->createtime,
                'visituser' => $v->userno,
                'typeid' => $v->modeltype);
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }
    /*
     * 服务拜访 新建服务或拜访
     * @param $isvisit 0为服务 1为拜访
     * @param $starttime  开始时间
     * @param $endtime 结束时间
     * @param $visittype 类型
     * @param $receptionname 客户公司名字
     * @param $remarker 批阅人
     * @param $waitername 拜访人员 或拜访人员 默认为当前登录人
     * @param $description 描述
     * @param $address 地点
     * @param $time 时间
     * @param $point 坐标
     * @param $type 2013-06-26 之前无自定义字段，不存在type，type为默认值-1, 4代表服务,5代表拜访
     */
    public function edit_visit($isvisit,$starttime,$endtime,$visittype,$receptionname,$customerid,$customername,$enterpriseno,$remarker,$waitername,$description,$attachmentid,$userno,$address,$time,$point,$type,$params){
        Doo::loadClass('Vist');
        Doo::loadClass('Exterprise');
        Doo::loadClass('SysMessage');
        /*
          新建服务是没有接待人员的 也就是 字段 customername
         */
        $fields['updatetime'] = date('Y-m-d H:i:s');
        $fields['createtime'] = date('Y-m-d H:i:s');
        $fields['starttime'] = date('Y-m-d H:i:s', strtotime($starttime));
        $fields['endtime'] = date('Y-m-d H:i:s', strtotime($endtime));
        $fields['visittypeid'] = $isvisit ? $visittype : ''; //拜访方式
        $fields['customerid'] = 0; //客户联系人id 这个字段没用
        $fields['customername'] = $isvisit ? $receptionname : ''; //接待人员
        $fields['profileid'] = $customerid; //客户公司id
        $fields['profileidname'] = $customername; //客户公司名字
        $fields['enterpriseno'] = $enterpriseno;
        $fields['modeltype'] = $isvisit ? 0 : 1;  //0是拜访 1是服务
        $fields['status'] = 1101;
        $fields['tousers'] = $remarker;  //批阅人
        $fields['visituser'] = $waitername; //拜访人员 或拜访人员 默认为当前登录人
        $fields['content'] = $description;
        $fields['attachmentids'] = $attachmentid;
        $fields['userno'] = $userno;
        $fields['address'] = $address;
        $fields['signtime'] = $time;
        $fields['point'] = $point;
        $fields['markinguser'] = '';
        $id = Vist::addVisit($fields);
        if (!$id) {
            $json = array('returncode'=>self::ERROR_CODE);
            return $json;
        }
        if($type != -1){
            Doo::loadClass('CustomForm');
            CustomForm::add_customform_data($userno, $enterpriseno, $type, $id, $params);  //服务拜访自定义字段值
        }
        $opt['where'] = 'visitid = ? AND enterpriseno = ?';
        $opt['param'] = array($id, $enterpriseno);
        $visit = Vist::getOneVisit($opt);
        Doo::loadClass('Today');
        Today::add_visit($visit);
        Vist::addSysLog($id, 102, $userno, '', $enterpriseno);
        //系统消息给服务拜访人
        if ($fields['modeltype'] == 0) {
            $modeltypename = '拜访';
        } else {
            $modeltypename = '服务';
        }
        $title = $fields['profileidname'] . $modeltypename . '记录';
        $url = "/visit/index?id=" . $id . "#/app_content/visit/page/visitinfo/" . $id;
        $userinfo = Exterprise::getUserInfo(array('where' => "enterpriseno={$enterpriseno} and userno={$userno}"));
        if($fields['tousers'] != ''){
            SysMessage::send_sysMessage($id, 'visit', 135, $userno, $userinfo->username, $enterpriseno, '', $fields['tousers'], array('title' => $title, 'url' => $url));
        }
        if($fields['visituser'] != ''){
            SysMessage::send_sysMessage($id, 'visit', 136, $userno, $userinfo->username, $enterpriseno, '', $fields['visituser'], array('title' => $title, 'url' => $url, 'modeltype' => $modeltypename));
        }
        $json = array('returncode'=>self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 服务拜访  获取拜访详情
     */

    public function get_visit_detail($enterpriseno, $visitid) {
        Doo::loadClass('Vist');
        Doo::loadClass('Enum');
        Doo::loadClass('Comment');
        Doo::loadClass('CustomForm');
        $normal = Enum::getStatusType('Normal');
        $opt = array(
            'where' => 'visitid=? AND enterpriseno=? AND status=?',
            'param' => array($visitid, $enterpriseno, $normal)
        );
        $result = Vist::getOneVisit($opt);
        if (!$result) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $data = array(
            'visittype' => $this->get_visit_typename($result->visittypeid), //拜访方式
            'waitername' => $result->visituser, //服务人员
            'reception_user' => $result->customername, //接待人员
            'createtime' => $result->createtime,
            'starttime' => $result->starttime,
            'themename' => $result->profileidname,
            'endtime' => $result->endtime,
            'location_address' => $result->address, //地理位置
            'location_time' => $result->signtime, //地理位置
            'location_point' => $result->point, //地理位置
            'description' => $result->content, //具体描述
            'annex' => $result->attachmentids, //附件列表(id:附件id，annexname:附件名字,annex_url:附件链接)
            'comment' => '', //最新一条评论 (c_id评论id,content评论内容)
            'remarkuser' => $result->tousers, //批阅人列表(userno批阅人id,username姓名,img_url头像)
            'markinguser' => $result->markinguser
        );
        $postmode = Enum::getCommentMode('Visit');
        $opt['where'] = 'postmode=? AND status=? AND postid=?';
        $opt['param'] = array($postmode, 1101, $visitid);
        $comment_count = Comment::coutComments($opt);
        $first_comment = Comment::getOneComment($opt);
        $data['comment_count'] = $comment_count;
        if ($first_comment != null) {
            $data['comment'] = $first_comment['username'] . ":" . $first_comment['content'];
        }
        $type = 5;  //0拜访
        if($result->modeltype){
            $type = 4;//1服务
        }
        $formdata = CustomForm::get_customform_detail($enterpriseno, $type, $visitid);
        $data['formdata'] = $formdata;
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $data);
        return $json;
    }
    /*
     * 服务拜访
     */
    private function get_visit_typename($id) {
        Doo::loadClass('Vist');
        $opt_z['where'] = 'visttypeid =?';
        $opt_z['param'] = array($id);
        $result_z = Vist::getOneVisitType($opt_z);
        if ($result_z != null){
            $visttype = $result_z->name;
        }else{
            $visttype = '';
        }
        return $visttype;
    }

    /*
     * 服务拜访  设置已读
     */

    public function set_visit_read($userno, $enterpriseno, $visitid) {
        Doo::loadClass('Vist');
        $opt['where'] = 'visitid =? AND status=? AND enterpriseno=?';
        $opt['param'] = array($visitid, 1101, $enterpriseno);
        $result = Vist::getOneVisit($opt);
        if (!$result) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        if (empty($result->markinguser)) {
            $result->markinguser = $userno;
            $result->update();
        } else {
            $makeusers = explode(',', $result->markinguser);
            if (!in_array($userno, $makeusers)) {
                array_push($makeusers, $userno);
                $result->markinguser = implode(',', $makeusers);
                $result->update();
            }
        }
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }
    /*
     * 服务拜访  删除服务拜访
     */
    public function del_visit($userno,$enterpriseno,$visitid){
        Doo::loadClass('Vist');
        $res = Vist::delete_visit($visitid, $userno, $enterpriseno);
        if($res['success']){
            $json = array('returncode'=>self::SUCCESS_CODE);
        }else{
            $json = array('returncode'=>self::ERROR_CODE);
        }
        return $json;
    }
    /*
     * 内部邮件  发送邮件
     * @param string $cctousers 抄送人id串
     */

    public function send_pms($userno, $enterpriseno, $title, $content, $readers, $attachmentid, $cctousers) {
        Doo::loadClass('Pms');
        Doo::loadClass('Exterprise');
        Doo::loadClass('Enum');
        $userinfo = Exterprise::getUserInfo(array('where' => "userno={$userno}"));
        $createtime = date('Y-m-d H:i:s');
        $viewusers = $userno;
        $lastreplytime = date('Y-m-d H:i:s');
        $lastreplyuser = $userno;
        $status = Enum::getStatusType('Normal');

        $id = Pms::add_pms(0, $userinfo->username, $userno, "", $readers, $enterpriseno, $title, $content, $viewusers, $lastreplytime, $lastreplyuser, $cctousers, $type = 0, $status = 1101, $attachmentid, 0, '', 0, 0, $createtime = '');
        if ($id >= 0) {
            Pms::addSysLog($id, 112, $userno, $userinfo->username, $enterpriseno);
            $json = array('returncode' => self::SUCCESS_CODE);
        } else {
            $json = array('returncode' => self::ERROR_CODE);
        }
        return $json;
    }

    /*
     * 内部邮件  收件箱列表
     * 筛选条件为该公司的邮件 and 用户不在删除队列 and (用户为发送人 and 回复数大于0)  或 用户在收件人队列且不是发送人 或 用户不为发送人受众为全部 或 用户不为发送人但在抄送人队列 and状态为正常
     * 删除邮件时只是把当前人id放到deleteusers字段中
     */

    public function get_inbox_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass('Pms');
        Doo::loadClass('Enum');
        Doo::loadClass('Exterprise');
        //获取账号创建时间
        $limit_time = "0000-00-00 00:00:00";
        $option['where'] = 'userno = ? AND enterpriseno = ?';
        $option['param'] = array($userno, $enterpriseno);
        $useremployee_model = Exterprise::getUserInfo($option);
        if ($useremployee_model != null) {
            $limit_time = $useremployee_model->createtime;
        }
        $normal = Enum::getStatusType('Normal');
        $opt = array(
            'where' => 'enterpriseno=? AND ((msgfromid = ?  AND replycount != ?) OR (FIND_IN_SET(?,msgtoid) AND msgfromid != ?) OR (msgfromid != ? AND msgtoid = ? AND createtime >= ?) OR (FIND_IN_SET(?,cctousers) AND msgfromid != ?))',
            'param' => array($enterpriseno, $userno, 0, $userno, $userno, $userno, 0, $limit_time, $userno, $userno),
            'desc' => 'lastreplytime'
        );
        $nowtime = date('Y-m-d H:i:s');
        if ($lasttime != -1 && $pageindex == 1) {
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $param = $opt['param'];
            array_push($param, $updatetime, $nowtime);
            $temp_opt = array(
                'where' => $opt['where'] . ' AND updatetime BETWEEN ? AND ?',
                'param' => $param
            );
            $count = Pms::count_pms($temp_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $opt['where'] .= ' AND status=? AND (NOT FIND_IN_SET(?,deleteusers))';
        array_push($opt['param'], $normal, $userno);
        $count = Pms::count_pms($opt);
        $page = ($pageindex - 1) * $pagesize;
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $reslist = Pms::get_all_pms_list($opt);
        $opt_reply['where'] = "pmsid = ? AND (NOT FIND_IN_SET(?,viewusers))";

        $list = array();
        if ($reslist != null) {
            foreach ($reslist as $v) {
                $user = Exterprise::getUserInfo(array("where" => "userno=" . $v->msgfromid));
                $opt_reply['param'] = array($v->pmsid, $userno);
                $reply_count = Pms::count_pmsreply($opt_reply);
                $v->reply_count = $reply_count; //未读回复总数
                $list[] = array(
                    "pmsid" => $v->pmsid,
                    "userid" => $v->msgfromid,
                    "username" => $user->username,
                    "createtime" => $v->createtime,
                    "title" => $v->subject,
                    "content" => $v->message,
                    "is_read" => strstr($v->viewusers, "$userno") ? 1 : 0,
                    "replay" => $v->replycount,
                    "reply_count" => $v->reply_count,
                );
            }
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 内部邮件  获取发件箱
     * 筛选条件为用户是发送人且不在deleteusers字段中
     */

    public function get_outbox_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass('Exterprise');
        Doo::loadClass('Pms');
        Doo::loadClass('Enum');

        $opt_pms['where'] = "enterpriseno = ? AND (NOT FIND_IN_SET(?,deleteusers)) AND msgfromid = ? ";
        $opt_pms['param'] = array($enterpriseno, $userno, $userno);
        $opt_pms['desc'] = 'lastreplytime';
        $nowtime = date('Y-m-d H:i:s');

        if ($lasttime != -1 && $pageindex == 1) {
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $tem_opt['where'] = "enterpriseno = ? AND msgfromid = ? AND updatetime BETWEEN '$updatetime' AND '" . $nowtime . "'";
            $tem_opt['param'] = array($enterpriseno, $userno);
            $count = Pms::count_pms($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $opt_pms['where'] .= " AND status=" . Enum::getStatusType('Normal');
        $count = Pms::count_pms($opt_pms);
        $page = ($pageindex - 1) * $pagesize;
        $opt_pms['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $reslist = Pms::get_all_pms_list($opt_pms);
        $list = array();
        if ($reslist != null) {
            foreach ($reslist as $v) {
                $joinusers = "";
                $userlist = Exterprise::getUserInfos(array("where" => "userno IN ($v->msgtoid) AND enterpriseno=$enterpriseno"));
                if ($userlist != null) {
                    foreach ($userlist as $v1) {
                        $joinusers.=',' . $v1->username;
                    }
                    $joinusers = trim($joinusers, ",");
                }
                $list[] = array(
                    "pmsid" => $v->pmsid,
                    "username" => $joinusers,
                    "createtime" => $v->createtime,
                    "title" => $v->subject,
                    "content" => $v->message,
                );
            }
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 内部邮件  获取内部邮件详情
     * 获取详情  设置当前用户为已读  获取相关投票详情
     */

    public function get_pms_detail($pmsid, $userno, $enterpriseno) {
        $vote_arr = array('1' => '同意', '2' => '已阅', '3' => '不同意', '4' => '待定', '5' => '弃权');
        Doo::loadClass('Vote');
        Doo::loadClass('Pms');
        Doo::loadClass('Exterprise');
        Doo::loadClass('Enum');
        //检测查看用户是否是收件人或抄送人,如果不是则无权查看该记录
        $normal = Enum::getStatusType('Normal');
        $opt_pms['where'] = "enterpriseno=? AND (msgfromid=? OR ((FIND_IN_SET(?,msgtoid) OR FIND_IN_SET(?,cctousers) OR msgtoid=?) AND status=?))";
        $opt_pms['param'] = array($enterpriseno, $userno, $userno, $userno, 0,$normal);
        $info = Pms::get_one_pms($pmsid,$opt_pms);
        if ($info === false) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $user = Exterprise::getUserInfo(array("where" => "userno=" . $info->msgfromid));
        $opt['where'] = 'pmsid=' . $pmsid;
        $opt['desc'] = 'createtime';
        $first_comment = Pms::get_one_pmsreply(null, $opt);
        $comment_count = Pms:: count_pmsreply($opt);
        if ($first_comment != null) {
            $first_comment = $this->get_user_info($first_comment->userno, 'username', $enterpriseno, true) . ":" . $first_comment->content;
        }
        $tousers = explode(',', $info->msgtoid);
        $prior_users = implode(',', array_slice($tousers, 0, 3));
        $pms_info = array(
            "userid" => $info->msgfromid,
            "username" => $user->username,
            'type' => $info->type,
            "createtime" => $info->createtime,
            "title" => $info->subject,
            'prior_users' => $prior_users,
            'firstcomment' => $first_comment,
            'commentcount' => $comment_count,
            "content" => $info->message,
            "readusers" => $info->msgtoid,
            "attachments" => $info->attachments,
            "voteid" => $info->votetopicid,
            'viewusers'=>$info->viewusers
        );
        $pos = strpos($info->viewusers, $userno);
        if ($pos === false) {
            $info->viewusers .= "," . $userno;
            trim($info->viewusers, ',');
            $info->update();
        }
        $pmsvote_list = Pms::find_pmsvote($info->pmsid);

        $detail = array();
        $data = array();
        foreach ($pmsvote_list as $v) {
            $voteinfo = Vote::get_vote_info($userno, $v->votetopicid, $enterpriseno);
            $vote = $voteinfo['data'];

            $data['maxvoteoption'] = $vote->maxvoteoption == 1 ? 1 : $vote->maxvoteoption;
            $data['is_read'] = $vote->hasvote ? 1 : 0;

            if ($v->type != 4) {     //这里为投票
                foreach ($vote->optionlist as $v1) {

                    $data['option'][] = array('votename' => $v1['optiontitle'], 'number' => $v1['times'], 'voteid' => $v1['voteoptionid']);
                }
                if ($vote->hasvote) {
                    foreach ($vote->loglist as $v3) {
                        if ($v3['userno'] == $userno) {
                            if ($v->type != 0) {
                                $data['is_read'] = array_search($v3['voteoptiontitles'], $vote_arr);
                            }
                        }
                    }
                }

                $detail[] = array('type' => $v->type, 'voteid' => $v->votetopicid, 'title' => $vote->title, 'showlog' => $vote->showlog, 'showresult' => $vote->showresult, 'data' => $data);
            } elseif ($v->type == 4) {
                $data['option'] = array();
                foreach ($vote->loglist as $v2) {

                    $data['option'][] = array('name' => $v2['username'], 'content' => $v2['voteoptiontitles'], 'time' => $v2['createtime']);
                }
                $detail[] = array(
                    'type' => $v->type,
                    'voteid' => $v->votetopicid,
                    'title' => $vote->title,
                    'showlog' => $vote->showlog,
                    'showresult' => $vote->showresult,
                    'data' => $data);
            }
            $data['option'] = null;
        }
        $pms_info['detail'] = $detail;
        $option = array('where' => 'enterpriseno=' . $enterpriseno);
        Pms::update_viewusers_pms($pmsid, $userno, false, $option);
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $pms_info);
        return $json;
    }

    /*
     * 内部邮件添加回复
     */

    public function reply_email($userno, $id, $content, $attachments) {
        Doo::loadClass('Pms');
        $resid = Pms::add_pmsreply($id, $userno, $content, $attachments, $userno, 1101, 1);
        if ($resid) {
            Pms::update_viewusers_pms($id, $userno, true);
            $json = array('returncode' => self::SUCCESS_CODE);
        } else {
            $json = array('returncode' => self::ERROR_CODE);
        }
        return $json;
    }

    /*
     * 内部邮件  获取回复列表
     * 注：未加企业no查询id 未分页
     */

    public function get_pmsreply_list($id) {
        Doo::loadClass('Exterprise');
        Doo::loadClass('Pms');
        $reslist = Pms::get_all_pmsreply_list(array('where' => "pmsid=$id", 'desc' => 'createtime'));
        $list = array();
        if ($reslist != null) {
            foreach ($reslist as $v) {
                $user = Exterprise::getUserInfo(array("where" => "userno=" . $v->userno));
                $list[] = array(
                    "userno" => $v->userno,
                    'username' => $user->username,
                    'content' => $v->content,
                    'attachments' => $v->attachments,
                    'createdate' => $v->createtime
                );
            }
        }
        $json = array('returncode' => self::SUCCESS_CODE, 'contents' => $list);
        return $json;
    }

    /*
     * 内部邮件  删除内部邮件
     */

    public function delete_pms($pmsid, $userno, $enterpriseno) {
        Doo::loadClass('Pms');
        $info = Pms::get_one_pms($pmsid);
        if ($info === false) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $result = Pms::delete_pms($pmsid, '', $userno, $enterpriseno);
        if ($result == -1) {
            $json = array('returncode' => self::ERROR_CODE);
        } else {
            $json = array('returncode' => self::SUCCESS_CODE);
            if ($info->status == '1101') {
                Pms::addSysLog($pmsid, 121, $userno, '', $enterpriseno);
            } else {
                Pms::addSysLog($pmsid, 122, $userno, '', $enterpriseno);
            }
        }
        return $json;
    }

    /*
     * 评论  获取评论列表
     */

    public function get_comment_list($userno,$enterpriseno, $id, $type, $pageindex, $pagesize,$canview) {
        Doo::loadClass('Enum');
        Doo::loadClass('Comment');
        Doo::loadClass('Exterprise');
        $postmode = Enum::getCommentMode($type);
        $status = Enum::getStatusType('Normal');
        $opt['where'] = 'postmode=? AND status=?';
        if ($id != 0) {
            if($canview){
                $opt['where'] = $opt['where'] . ' AND postid=?';
                $opt['param'] = array($postmode, $status, $id);
            }else{
                $user = Exterprise::getUserEmployeeInfo(array('where' => "userno={$userno} AND enterpriseno={$enterpriseno}"));
                $opt = array(
                    'where'=>'postmode=? AND status=? AND postid=? AND (userno=? OR content LIKE ?)',
                    'param'=>array($postmode,$status,$id,$userno,"%@$user->employeename%")
                );
            }
            
            
        } else {
            $opt['param'] = array($postmode, $status);
        }
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $list = array();
        $opt['desc'] = 'level';
        $comments = Comment::getCommentInfo($opt);

        if ($comments != null) {
            foreach ($comments as $v) {
                foreach ($v as $v1) {
                    $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$v1['userno']} AND enterpriseno={$enterpriseno}"));
                    $list[] = array(
                        'userno' => $v1['userno'],
                        'username' => $userinfo->employeename,
                        'content' => $v1['content'],
                        'createdate' => $v1['createtime'],
                        'attachments' => $v1['attachmentids']
                    );
                }
            }
        }
        $json = array('returncode' => self::SUCCESS_CODE, 'contents' => $list);
        return $json;
    }

    /*
     * 评论  添加评论
     */

    public function add_comment($userno, $enterpriseno, $id, $type, $content) {
        Doo::loadClass('Enum');
        Doo::loadClass('Common');
        Doo::loadClass('Comment');
        Doo::loadClass('Exterprise');
        $opt['select'] = 'MAX(level) AS lv';
        $opt['where'] = 'postmode=? AND status=? AND postid=?';
        $status = Enum::getStatusType('Normal');
        $opt['param'] = array(Enum::getCommentMode($type), $status, $id);
        $array = Comment::getOneComment($opt);
        $level = $array['lv'] + 1;
        $userinfo = Exterprise::getUserEmployeeInfo(array('where' => "userno={$userno} AND enterpriseno={$enterpriseno}"));

        $option = array(
            'postid' => $id,
            'postmode' => Enum::getCommentMode($type),
            'userno' => $userno,
            'username' => $userinfo->employeename,
            'postip' => Common::getIP(),
            'createtime' => date('Y-m-d H:i:s'),
            'content' => $content,
            'level' => $level,
            'status' => $status,
            'from' => 1
        );
        $id = Comment::addComment($option);
        if ($id > 0) {
            $json = array('returncode' => self::SUCCESS_CODE);
        } else {
            $json = array('returncode' => self::ERROR_CODE);
        }
        return $json;
    }

    /*
     * 手机注册  获取申请加入信息
     */

    public function get_reg_message($userno, $enterpriseno, $pageindex, $pagesize) {
        Doo::loadClass('SysMessage');
        Doo::loadModel("EnterpriseMsgcenter");
        $feed = new EnterpriseMsgcenter();
        $opt = array(
            'where' => 'isprocessed=? AND hasprocessed=? AND (FIND_IN_SET(?,tousers) OR tousers=?) AND fromuserno!=? AND enterpriseno=?',
            'param' => array(1, 0, $userno, 0, $userno, $enterpriseno),
        );
        $count = $feed->count($opt);
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $list = SysMessage::find_sysMessage($opt);
        $nowtime = strtotime("today");
        $msglist = array();
        foreach ($list as $v) {
            $item["nowtime"] = $nowtime;
            $item["userlogo"] = "";
            $item["name"] = $v->fromuser;
            $item["applayuserno"] = $v->fromuserno;
            $item['enterpriseno'] = $v->enterpriseno;
            $item["type"] = $v->idtype;
            $item["id"] = $v->id;
            $item["title"] = $v->title_template;
            $item["content"] = $v->content_template;
            $item["body"] = $v->content_data;
            $item["createtime"] = $v->createtime;
            $item["msgid"] = $v->msgcenterid;
            $item["itemcount"] = $count;
            $item['is_read'] = strstr($v->viewusers, "$userno") ? true : false;
            $msglist[] = $item;
        }
        $json = array('returncode' => self::SUCCESS_CODE, 'contents' => $msglist);
        return $json;
    }

    /*
     * 手机注册  同意加入公司 
     */

    public function agree_join_enterprise($operateuserno, $enterpriseno, $userno, $id) {
        $roleid = 1; //默认权限组
        //判断该消息是否可处理状态
        Doo::loadClass('SysMessage');
        Doo::loadClass('Exterprise');
        $sysmsgmodel = SysMessage::getOneSysMsg(array('where' => 'msgcenterid=?', 'param' => array($id)));
        if ($sysmsgmodel == null) {
            $json = array('returncode' => self::CANCLE_APPLY_CODE);
            return $json;
        }
        if ($sysmsgmodel->hasprocessed != 0) {
            $json = array('returncode' => self::PROCESSED_CODE);
            return $json;
        }
        //获取用户信息
        Doo::loadClass('OAClient');
        $userinfo = OAClient::get_user_info($userno);
        $info = json_decode($userinfo);
        $user = $info->data;
        $email = $user->email;
        //获取员工信息
        $re = Exterprise::get_enterpriseuser_accountinfo($enterpriseno, $user->email);
        if ($re['existenterprise']) {
            //已经加入公司
            $sysmsgmodel->delete();
            $json = array('returncode' => self::JOINED_CODE);
            return $json;
        }
        $employee = Exterprise::get_employee_byemail($email, $enterpriseno);
        //不存在的员工;否则修改用户状态
        if ($employee == NULL) {
            //创建员工(档案表)
            $employee = Exterprise::add_employee($user->username, $user->email, $enterpriseno);
            //创建失败
            if ($employee == null) {
                $json = array('returncode' => self::FAIL_ADDUSER_CODE);
                return $json;
            }
        } else {
            if ($employee->status == 1101) {
                
            } else {
                $affectnunber = Exterprise::update_employee($email, $enterpriseno);
                if ($affectnunber < 0) {
                    $json = array('returncode' => self::FAIL_UPDATEUSER_CODE);
                    return $json;
                }
            }
        }
        //检查关联表是否存在
        $ue = Exterprise::getUserInfo(array('where' => 'enterpriseno=' . $enterpriseno . " and userno=$userno"));
        if ($ue) {
            Exterprise::update_user_employee($email, $enterpriseno);
        } else {
            //关联用户&员工
            $res = Exterprise::create_user_employee($enterpriseno, $user, $employee->employeeid, $roleid);
        }
        //同步OA添加员工
        Doo::loadClass('OAClient');
        $res = OAClient::append_enterprise_user($userno, $enterpriseno);
        $resone = json_decode($res);
        if ($resone->error) {
            $json = array('returncode' => self::ERROR_CODE);
            return $json;
        }
        $restwo = OAClient::add_tpnumber($enterpriseno, $user->mobile);
        $resthree = json_decode($restwo);
        if ($resthree->error) {
            $json = array('returncode' => self::LICENSE_ERROR_CODE);
            return $json;
        }
        $resfour = OAClient::agree_userjoin($userno, $enterpriseno, $operateuserno);
        $resfive = json_decode($resfour);
        if (!$resfive->success) {
            $json = array('returncode' => self::REFUSE_ERROR_CODE);
            return $json;
        } else {
            //发送一条新鲜事 20110601
            Doo::loadClass('Feed');
            $colleaguesname['colleaguserno'] = $user->userno;
            $colleaguesname['colleaguesname'] = $user->username;
            $admininfo = Exterprise::getAdminList('1', $enterpriseno);
            foreach ($admininfo as $v) {
                Feed::publish_feed('', 'platformid', 33, $v['userno'], $v['username'], $enterpriseno, '', $colleaguesname, '', 1);
            }
            //更新系统消息状态
            $values = array('hasprocessed' => 1);
            $opt = array('where' => 'msgcenterid=?', 'param' => array($id));
            SysMessage::updateSysMsg($values, $opt);
        }
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 手机注册  拒绝加入公司
     */

    public function refuse_join_enterprise($operateuserno, $enterpriseno, $userno, $id) {
        Doo::loadClass('SysMessage');
        $sysmsgmodel = SysMessage::getOneSysMsg(array('where' => 'msgcenterid=?', 'param' => array($id)));
        if ($sysmsgmodel == null) {
            $json = array('returncode' => self::CANCLE_APPLY_CODE);
            return $json;
        }
        if ($sysmsgmodel->hasprocessed != 0) {
            $json = array('returncode' => self::PROCESSED_CODE);
            return $json;
        }
        Doo::loadClass('OAClient');
        Doo::loadClass('Exterprise');
        //获取员工信息
        $userinfo = OAClient::get_user_info($userno);
        $user = json_decode($userinfo);
        $re = Exterprise::get_enterpriseuser_accountinfo($enterpriseno, $user->data->email);
        if ($re['existenterprise']) {
            //已经加入公司
            $sysmsgmodel->delete();
            $json = array('returncode' => self::JOINED_CODE, 'data' => 'delete');
            return $json;
        }
        $resone = OAClient::refuse_userjoin($userno, $enterpriseno, $operateuserno);
        $res = json_decode($resone);
        if (!$res->success) {
            $json = array('returncode' => self::REFUSE_ERROR_CODE);
            return $json;
        } else {
            //删除员工（档案）
            //更新系统消息状态
            Doo::loadClass('SysMessage');
            $values = array('hasprocessed' => 2);
            $opt = array('where' => 'msgcenterid=?', 'param' => array($id));
            SysMessage::updateSysMsg($values, $opt);
        }
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }

    /*
     * 手机注册  帮助注册
     */

    public function help_join_enterprise($name, $salt, $department, $position, $role, $status, $mobile, $enterpriseno, $user_email) {
        Doo::loadClass('newSys');
        $result = Company::invite_employee_join($user_email, $name, $enterpriseno, $status, '', $mobile, $department, $position, $role, $salt, '');
        if (is_array($result)) {
            $json = array('returncode' => self::ERROR_CODE, 'message' => $result);
        } else {
            $json = array('returncode' => self::SUCCESS_CODE);
        }
        return $json;
    }

    /*
     * 网络传真  收件箱列表
     */

    public function get_faxinbox_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass('eFax');
        Doo::loadClass('Enum');
        $uc = eFax::get_efaxno_fromuserno($userno, $enterpriseno);
        $opt['where'] = ' status = ?' . " and enterpriseno = $enterpriseno";
        $opt['param'] = array(Enum::getStatusType('Normal'));
        $opt['desc'] = "createtime";
        if ($uc != null && count($uc) > 0) {
            $arr_efaxno = array();
            foreach ($uc as $v) {
                $arr_efaxno[] = $v->efaxno;
            }
            if (count($arr_efaxno) > 0) {
                $str_efaxno = implode(",", $arr_efaxno);
                $opt['where'] = $opt['where'] . " and tofax in(" . $str_efaxno . ")";
            }
        } else {
            $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
            return $json;
        }
        Doo::loadModel('EnterpriseEfaxInbox');
        $model = new EnterpriseEfaxInbox();
        $nowtime = date('Y-m-d H:i:s');

        if ($lasttime != -1 && $pageindex == 1) {
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $tem_opt['where'] = $opt['where'] . " and createtime between '$updatetime' and '" . $nowtime . "'";
            $tem_opt['param'] = $opt['param'];
            $count = $model->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = $model->count($opt);
        $page = ($pageindex - 1) * $pagesize;
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $result = eFax::findInBoxFax($opt);
        $list = array();
        foreach ($result as $value) {
            $list[] = array(
                "id" => $value->inboxid,
                "title" => $value->title,
                "sender" => $value->fromfax,
                "status" => $value->status,
                "createtime" => $value->createtime,
                'isread' => $value->isread,
            );
        }//findOutBoxFax
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 网络传真  获取发件箱列表
     */

    public function get_faxoutbox_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass('eFax');
        Doo::loadClass('Enum');
        $opt['desc'] = 'createtime';
        $opt['where'] = 'status != ?' . " and enterpriseno = $enterpriseno" . " and userno =$userno";
        $opt['param'] = array(Enum::getStatusType('Draft'));
        Doo::loadModel('EnterpriseEfaxOutbox');
        $model = new EnterpriseEfaxOutbox();
        $nowtime = date('Y-m-d H:i:s');
        if ($lasttime != -1 && $pageindex == 1) {
            $tem_opt['where'] = $opt['where'] . " and createtime between '$lasttime' and '" . $nowtime . "'";
            $tem_opt['param'] = $opt['param'];
            $count = $model->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = $model->count($opt);
        $page = ($pageindex - 1) * $pagesize;
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $result = eFax::findOutBoxFax($opt);
        $list = array();
        foreach ($result as $value) {
            $opt_rec['where'] = 'boxid = ' . $value->boxid;
            $opt_rec['limit'] = 1;
            $model_efax_receipt = eFax::findFaxReceipt($opt_rec);
            if ($model_efax_receipt == null) {
                $falg = 1;
            } else {
                $falg = array_search($model_efax_receipt->flag, array('S', 'F', 'D'));
            }
            $list[] = array(
                "id" => $value->boxid,
                "title" => $value->title,
                "status" => $falg,
                "createtime" => $value->createtime,
            );
        }//findOutBoxFax
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $list, 'deletes' => array());
        return $json;
    }

    /*
     * 网络传真  发送传真
     */

    public function send_fax($userno, $enterpriseno, $destnumber, $title, $files, $sendtime, $content) {
        Doo::loadClass('eFax');
        Doo::loadClass('Enum');
        Doo::loadClass('Common');
        $status = Enum::getStatusType('Normal');
        if (!empty($content)) {
            Doo::loadClass("Attachment");
            $arr = Attachment::createHtmlAttachment($userno, $enterpriseno, $content);
            if (count($arr) > 0 && isset($arr['attachmentid'])) {
                $files = Common::append_to_subject($arr['attachmentid'], $files);
            }
        }
        $c = eFax::get_enterprise_config($enterpriseno);
        //判断用户是否有配置传真账号
        $uc = eFax::get_efaxno_fromuserno($userno, $enterpriseno);
        //是否有权限发传真
        if (!$this->is_sendefax($userno, $enterpriseno)) {
            $json = array('returncode' => self::NO_PERMISSION_CODE);
            return $json;
        }
        if (empty($uc) && empty($c)) {
            $json = array('returncode' => self::NO_FAXNUMBER_CODE);
            return $json;
        } elseif (!empty($uc)) {
            $sender = isset($uc) ? $uc[0] : "";
        } else {
            $sender = isset($c) ? $c[0] : "";
        }
        $res = eFax::addOutBoxFax($destnumber, $title, $files, $sendtime, $enterpriseno, $userno, $status, $sender->efaxno);
        if ($res > 0) {
            $json = array('returncode' => self::SUCCESS_CODE);
            eFax::efax_send_cron();
        } else {
            $json = array('returncode' => self::ERROR_CODE);
        }
        return $json;
    }
    /*
     * 网络传真 是否有权限发送传真
     */
    private function is_sendefax($userno, $enterpriseno) {
        //in_array array_search 字符串不能在二维数组中匹配其中的元素
        Doo::loadClass('eFax');
        $arr_user = array();
        $c_sys = eFax::count_systemrole($userno, $enterpriseno);
        if ($c_sys > 0)
            return true;
        //查找所有的配置
        $c_opt['where'] = "enterpriseno = ? and parentid = ?";
        $c_opt['param'] = array($enterpriseno, 0);
        $c_list = eFax::findConfigFaxInfo($c_opt);
        if ($c_list != null) {
            foreach ($c_list as $v) {
                if ($v->checkusers != "") {
                    $arr_user = explode(",", $v->checkusers);
                    if (in_array($userno, $arr_user))
                        return true;
                }
                if ($v->uncheckusers != "") {
                    $arr_user = explode(",", $v->uncheckusers);
                    if (in_array($userno, $arr_user))
                        return true;
                }
            }
        }
        return false;
    }

    /*
     * 网络传真  获取传真详情
     */

    public function get_fax_detail($faxid, $type) {
        Doo::loadClass('eFax');
        if ($type) {
            $fax_info = eFax::getOutBoxFoxInfo($faxid);
        } else {
            $fax_info = eFax::getInBoxFaxInfo($faxid);
        }
        if ($fax_info === false) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        eFax::updateInBoxFaxisRead($faxid);
        $v = array(
            'title' => $fax_info->title,
            'sender' => isset($fax_info->fromfax) ? $fax_info->fromfax : $fax_info->sender,
            'receiver' => isset($fax_info->tofax) ? $fax_info->tofax : $fax_info->destnumber,
            'createtime' => $fax_info->createtime,
            'attachments' => $fax_info->files,
        );
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $v);
        return $json;
    }

    /*
     * 任务  获取我的任务列表
     */

    public function get_mytask_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass("Enum");
        Doo::loadClass("User");
        Doo::loadClass("Project");
        Doo::loadModel("EnterpriseTasks");
        $checked = Enum::getStatusType('Checked');
        $opt = array(
            'where' => 'enterpriseno=? and FIND_IN_SET(?,cousernos) and status>?',
            'param' => array($enterpriseno, $userno, $checked),
            'desc' => 'createtime'
        );
        $task = new EnterpriseTasks();
        $nowtime = date('Y-m-d H:i:s');
        if ($lasttime != -1 && $pageindex == 1) {
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $tem_opt['where'] = "enterpriseno=" . $enterpriseno . " and lastupdatetime between '$updatetime' and '" . $nowtime . "'";
            $count = $task->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = $task->count($opt);
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $result = Project::findTasks($opt);
        $tasklist = array();
        $status = array('1101' => 1, '1102' => 2, '1104' => 3, '1201' => 4, '1106' => 5, '1103' => 6);
        foreach ($result as $value) {
            $joinusers = array();
            $accepts = array();
            $a = array();
            $b = array();
            if (!empty($value->cousernos)) {
                $cousernos = explode(',', $value->cousernos);
                $a = array_filter($cousernos);
            }
            if (!empty($value->accepteruserid)) {
                $accepteruserid = explode(',', $value->accepteruserid);
                $b = array_filter($accepteruserid);
            }
            $c = array_merge($a, $b);
            if (count($c) > 0) {
                $arr = implode(',', $c);
                $list = User::find_user_list(array("where" => "userno in ($arr)"));
                if ($list != null) {
                    foreach ($list as $val) {
                        if (in_array($val->userno, $a)) {
                            $joinusers[] = $val->username;
                        }

                        if (in_array($val->userno, $b)) {
                            $accepts[] = $val->username;
                        }
                    }
                }
            }

            $tasklist[] = array(
                "projectid" => $value->projectid,
                "taskid" => $value->taskid,
                "taskname" => $value->taskname,
                "startdate" => $value->startdate,
                "finishdate" => $value->finishdate,
                "percentcomplete" => $value->percentcomplete,
                "notes" => $value->notes,
                "avatars" => '',
                'createtime' => $value->createtime,
                "joinuserids" => $value->cousernos,
                "joinusers" => implode(",", $joinusers),
                "accepter" => implode(',', $accepts),
                "status" => $status["$value->status"],
                'modelid'=>$value->modelid
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $tasklist, 'deletes' => array());
        return $json;
    }

    /*
     * 任务  获取我验收的任务列表
     */

    public function get_accepttask_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadClass("Project");
        Doo::loadClass("Enum");
        Doo::loadClass("User");
        Doo::loadModel('EnterpriseTasks');
        $checked = Enum::getStatusType('Checked');
        $normal = Enum::getStatusType('Normal');
        $opt = array(
            'where' => 'status>=? and enterpriseno=? and status>=? and FIND_IN_SET(?,accepteruserid)',
            'param' => array($checked, $enterpriseno, $normal, $userno),
            'desc' => 'createtime'
        );
        $task = new EnterpriseTasks();
        $nowtime = date('Y-m-d H:i:s');
        if ($lasttime != -1 && $pageindex == 1) {
            $updatetime = date('Y-m-d H:i:s', $lasttime);
            $tem_opt['where'] = "enterpriseno=" . $enterpriseno . " and lastupdatetime between '$updatetime' and '" . $nowtime . "'";
            $count = $task->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = $task->count($opt);
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $result = Project::findTasks($opt);
        $status = array('1101' => 1, '1102' => 2, '1104' => 3, '1201' => 4, '1106' => 5, '1103' => 6, '1015' => 7);
        $tasklist = array();
        foreach ($result as $value) {
            $joinusers = array();
            $accepts = array();
            $a = array();
            $b = array();
            if (!empty($value->cousernos)) {
                $cousernos = explode(',', $value->cousernos);
                $a = array_filter($cousernos);
            }
            if (!empty($value->accepteruserid)) {
                $accepteruserid = explode(',', $value->accepteruserid);
                $b = array_filter($accepteruserid);
            }
            $c = array_merge($a, $b);
            if (count($c) > 0) {
                $arr = implode(',', $c);
                $list = User::find_user_list(array("where" => "find_in_set (userno,?)", 'param' => array($arr)));
                if ($list != null) {
                    foreach ($list as $val) {
                        if (in_array($val->userno, $a)) {
                            $joinusers[] = $val->username;
                        }
                        if (in_array($val->userno, $b)) {
                            $accepts[] = $val->username;
                        }
                    }
                }
            }
            $tasklist[] = array("projectid" => $value->projectid,
                "taskid" => $value->taskid,
                "taskname" => $value->taskname,
                "startdate" => $value->startdate,
                "finishdate" => $value->finishdate,
                "percentcomplete" => $value->percentcomplete,
                "notes" => $value->notes,
                "avatars" => '',
                "joinuserids" => $value->cousernos,
                "joinusers" => implode(",", $joinusers),
                "accepter" => implode(',', $accepts),
                "creator" => $value->creator,
                "createtime" => $value->createtime,
                "status" => $status["$value->status"],
                'modelid'=>$value->modelid
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $tasklist, 'deletes' => array());
        return $json;
    }

    /*
     * 任务 获取共享给我的任务
     */

    public function get_all_task($userno, $enterpriseno, $pageindex, $pagesize, $lasttime) {
        Doo::loadModel('EnterpriseTasks');
        Doo::loadClass("Project");
        Doo::loadClass("Enum");
        Doo::loadClass("User");

        $opt['desc'] = 'createtime';
        $checked = Enum::getStatusType('Checked');
        $normal = Enum::getStatusType('Normal');
        $opt['where'] = 'status>=? and enterpriseno=? and ((viewpermission=? or(viewpermission=? and FIND_IN_SET(?,viewusers)) or FIND_IN_SET(?,joinusers) or 
                        FIND_IN_SET(?,accepteruserid) or FIND_IN_SET(?,cousernos) or creator=?)) and status>=? and (viewpermission=? or (viewpermission=? and FIND_IN_SET(?, viewusers)))';
        $opt['param'] = array($checked, $enterpriseno, 1, 3, $userno, $userno, $userno, $userno, $userno, $normal, 1, 3, $userno);
        $task = new EnterpriseTasks();
        $nowtime = date('Y-m-d H:i:s');

        if ($lasttime != -1 && $pageindex == 1) {
            $tem_opt['where'] = "enterpriseno=" . $enterpriseno . " and lastupdatetime between '$lasttime' and '" . $nowtime . "'";
            $count = $task->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode' => self::SUCCESS_CODE, 'contents' => array());
                return $json;
            }
        }
        $count = $task->count($opt);
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $result = Project::findTasks($opt);
        $status = array('1101' => 1, '1102' => 2, '1104' => 3, '1201' => 4, '1106' => 5, '1103' => 6, '1015' => 7);
        $tasklist = array();
        foreach ($result as $value) {
            $joinusers = array();
            $accepts = array();
            $a = array();
            $b = array();
            if (!empty($value->cousernos)) {
                $cousernos = explode(',', $value->cousernos);
                $a = array_filter($cousernos);
            }
            if (!empty($value->accepteruserid)) {
                $accepteruserid = explode(',', $value->accepteruserid);
                $b = array_filter($accepteruserid);
            }
            $c = $a + $b;
            if (count($c) > 0) {
                $arr = implode(',', $c);
                $list = User::find_user_list(array("where" => "userno in ($arr)"));
                if ($list != null) {
                    foreach ($list as $val) {
                        if (in_array($val->userno, $a)) {
                            $joinusers[] = $val->username;
                        }
                        if (in_array($val->userno, $b)) {
                            $accepts[] = $val->username;
                        }
                    }
                }
            }

            $tasklist[] = array("projectid" => $value->projectid,
                "taskid" => $value->taskid,
                "taskname" => $value->taskname,
                "startdate" => $value->startdate,
                "finishdate" => $value->finishdate,
                "percentcomplete" => $value->percentcomplete,
                "notes" => $value->notes,
                "avatars" => '',
                "createtime" => $value->createtime,
                "joinuserids" => $value->cousernos,
                "joinusers" => implode(",", $joinusers),
                "accepter" => implode(',', $accepts),
                "creator" => $value->creator,
                "status" => $status["$value->status"]
            );
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $tasklist, 'deletes' => array());
        return $json;
    }

    /*
     * 任务  新增 编辑任务
     * @param $joinusers  任务负责人
     */

    public function edit_task($userno, $enterpriseno, $startdate, $finishdate, $notes, $priority, $joinusers, $accepteruserid, $taskname, $attachmentids, $cousernos, $parentid, $taskid,$viewpermission,$viewusers,$modelid) {
        Doo::loadClass('Project');
        Doo::loadClass('Exterprise');
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        $curuser = Exterprise::getUserInfo(array('where' => "userno={$userno}"));
        $model = array(
            'finishdate' => $finishdate,
            'startdate' => $startdate,
            'notes' => $notes,
            'priority' => $priority,
            'joinusers' => $joinusers,
            'accepteruserid' => $accepteruserid,
            'taskname' => $taskname,
            'attachmentids' => $attachmentids,
            'cousernos' => $cousernos,
            'parentid' => $parentid,
            'status'=> $normal,
            'viewpermission'=>$viewpermission,
            'viewusers'=>$viewusers,
            'modelid'=>$modelid
        );
        if (empty($taskid)) {
            if (empty($joinusers)) {
                $tem_arr = explode(',', $cousernos);
                $joinusers = $tem_arr[0];
            }
            $res = Project::add_task($model, $curuser, $enterpriseno);
        } else {
            $res = Project::edit_task($model, $taskid, $curuser, $enterpriseno);
        }
        if ($res['success']) {
            $json = array('returncode' => self::SUCCESS_CODE);
        } else {
            $json = array('returncode'=>self::ERROR_CODE,'message'=>$res['message']);
        }
        return $json;
    }

    /*
     * 任务  执行任务
     */

    public function run_task($userno, $taskid) {
        Doo::loadClass('Project');
        Doo::loadClass('Exterprise');
        $curuser = Exterprise::getUserInfo(array('where' => "userno={$userno}"));
        $res = Project::execute_task($taskid, $curuser);
        if ($res['success']) {
            $json = array('returncode' => self::SUCCESS_CODE);
        } else {
            $json = array('returncode' => self::ERROR_CODE, 'message' => $res['message']);
        }
        return $json;
    }

    /*
     * 任务  提交任务进度
     */

    public function save_task_progress($userno, $taskid, $progress) {
        Doo::loadClass('UserCommon');
        Doo::loadClass('Project');
        Doo::loadClass('Exterprise');
        $curuser = Exterprise::getUserInfo(array('where' => "userno={$userno}"));
        $data = UserCommon::getViewData();
        $res = Project::save_task_progress($taskid, $progress, $curuser, $data['baseurl']);
        if ($res['success']) {
            $json = array('returncode' => self::SUCCESS_CODE);
        } else {
            $json = array('returncode' => self::ERROR_CODE);
        }
        return $json;
    }

    /*
     * 任务   验收任务
     */

    public function accept_task($userno, $taskid, $content, $status) {
        Doo::loadClass('Project');
        Doo::loadClass('Exterprise');
        Doo::loadClass('Enum');
        $curuser = Exterprise::getUserInfo(array('where' => "userno={$userno}"));
        $res = Project::accepting_task($taskid, $curuser, $status, '', $content);
        if ($res['success']) {
            $json = array('returncode' => self::SUCCESS_CODE);
        } else {
            $json = array('returncode' => self::ERROR_CODE, 'message' => $res['message']);
        }
        return $json;
    }

    /*
     * 任务  获取任务详情
     */

    public function get_task_detail($enterpriseno, $taskid,$modelid) {
        Doo::loadClass("Project");
        Doo::loadClass("User");
        Doo::loadClass("Enum");
        Doo::loadClass("Comment");
        Doo::loadClass("Exterprise");

        $taskinfo = Project::get_task_byid($taskid);
        if (empty($taskinfo)) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        $joinusers = $this->get_user_info($taskinfo->cousernos, 'username', $enterpriseno);
        $accepter = $this->get_user_info($taskinfo->accepteruserid, 'username', $enterpriseno);
        $status = array('1101' => 1, '1102' => 2, '1104' => 3, '1201' => 4, '1106' => 5, '1103' => 6, '1021' => 0);
        $postmode = Enum::getCommentMode('Task');
        $opt = array(
            'where'=>'postmode=? and status=? and postid=?',
            'param'=>array($postmode,1101,$taskid)
        );
        $first_comment = Comment::getOneComment($opt);
        $comment_count = Comment::coutComments($opt);
        if ($first_comment != null) {
            $first_comment = $first_comment['username'] . ":" . $first_comment['content'];
        }
        $tousers = explode(',', $taskinfo->joinusers);
        $prior_users = implode(',', array_slice($tousers, 0, 3));
        $content = array(
            "taskid" => $taskinfo->taskid,
            "taskname" => $taskinfo->taskname,
            "startdate" => $taskinfo->startdate,
            "finishdate" => $taskinfo->finishdate,
            "percentcomplete" => $taskinfo->percentcomplete,
            "notes" => $taskinfo->notes,
            'accepteruserid' => $taskinfo->accepteruserid,
            'joinusersid' => $taskinfo->cousernos,
            "joinusers" => $joinusers,
            'handeler' => $taskinfo->joinusers,
            "accepter" => $accepter,
            'creator' => $taskinfo->creator,
            "prority" => $taskinfo->priority,
            'prior_users' => $prior_users,
            'commentcount' => $comment_count,
            'firstcomment' => $first_comment,
            "attachments" => $taskinfo->attachmentids,
            "status" => @$status["$taskinfo->status"]
        );
        if ($content['status'] == 4) {
            $content['accepteruserid'] = $taskinfo->accepteruserid;
            $log = Project::getpasslog($taskinfo->taskid);

            $content['acceptancetime'] = !empty($log->createtime) ? $log->createtime : "";
            $content['username'] = $this->get_user_info($taskinfo->accepteruserid, 'username', $enterpriseno);
        }
        $subtask = Project::get_subsubtasks($taskinfo->taskid, $taskinfo->catelog, $modelid);
        $s = array();
        foreach ($subtask as $value) {
            $s[] = array('taskid' => $value->taskid,
                "taskname" => $value->taskname,
                "startdate" => $value->startdate,
                "finishdate" => $value->finishdate,
                'joinusersid' => $value->joinusers,
                'status' => @$status["$value->status"],
                'parentid' => $value->parentid,
                'deep' => $value->outlinelevel,
                "joinusers" => $this->get_user_info($value->joinusers, 'username', $enterpriseno),
            );
        }
        $content['subtask'] = $s;
        $json = array('returncode' => self::SUCCESS_CODE, 'content' => $content);
        return $json;
    }

    /*
     * 任务  申请验收任务
     */

    public function apply_accept($taskid, $userno) {
        Doo::loadClass('UserCommon');
        Doo::loadClass('Project');
        Doo::loadClass('Exterprise');
        $curuser = Exterprise::getUserInfo(array('where' => "userno={$userno}"));
        $data = UserCommon::getViewData();
        $task = Project::get_task_byid($taskid);
        if (empty($task)) {
            $json = array('returncode' => self::NO_RECODE_CODE);
            return $json;
        }
        Project::apply_accepting($task, $data['baseurl'], $curuser);
        $json = array('returncode' => self::SUCCESS_CODE);
        return $json;
    }
    /*
     * 任务   删除任务
     */
    public function del_task($taskid,$userno,$enterpriseno){
        Doo::loadClass('Project');
        Doo::loadClass('Exterprise');
        Doo::loadClass('Permission');
        $curuser = Exterprise::getUserInfo(array('where' => "userno={$userno}"));
        $task = Project::get_task_byid($taskid);
        $isadmin = Permission::checkUserPermission(ModuleCode::$Task, ActionCode::$Admin,$userno,$enterpriseno); //管理员
        $res = Project::del_task($task, $curuser, $isadmin);
        if($res['success']){
            $json = array('returncode'=>self::SUCCESS_CODE);
        }else{
            $json = array('returncode'=>self::ERROR_CODE);
        }
        return $json;
    }
    /*
     * 系统消息  获取系统消息列表
     * 筛选条件是当前用户在tousers字段中或者接收人为全部人 但是不是发送者
     */
    public function get_sysmsg_list($userno,$enterpriseno,$pageindex,$pagesize,$lasttime){
        Doo::loadClass('SysMessage');
        Doo::loadModel("EnterpriseMsgcenter");
        $feed = new EnterpriseMsgcenter();
        $opt = array(
            'where'=>'(FIND_IN_SET(?,tousers) or tousers=?) and fromuserno!=? and enterpriseno=?',
            'param'=>array($userno,0,$userno,$enterpriseno),
            'desc'=>'createtime'
        );
        if ($lasttime != -1 && $pageindex == 1) {
            $nowtime = date('Y-m-d H:i:s');
            $updatetime = date('Y-m-d H:i:s',$lasttime);
            $tem_opt = $opt;
            $tem_opt['where'] .= " and createtime between '$updatetime' and '" . $nowtime . "'";
            $count = $feed->count($tem_opt);
            if ($count == 0) {
                $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>array());
                return $json;
            }
        }
        $count = $feed->count($opt);
        $page = (($pageindex - 1) * $pagesize);
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $list = SysMessage::find_sysMessage($opt);
        SysMessage::setSysMessageIsRead($opt, $userno);
        $msglist = array();
        foreach ($list as $v) {
            $item["name"] = $v->fromuser;
            $item["type"] = $v->idtype;
            $item["id"] = $v->id;
            $item["title"] = $v->title_template;
            $item["content"] = $v->content_template;
            $item["createtime"] = $v->createtime;
            $item["msgid"] = $v->msgcenterid;
            $item['is_read'] = strstr($v->viewusers, "$userno") ? true : false;
            $msglist[] = $item;
        }
        $now = time();
        $json = array('returncode' => self::SUCCESS_CODE, 'total' => $count, 'nowtime' => $now, 'contents' => $msglist, 'deletes' => array());
        return $json;
    }
    /*
     * 系统消息  MOBI端获取最后一条推送消息
     */
    public function get_final_notification($userno,$enterpriseno){
        Doo::loadClass("Notification");
        $m = Notification::getLastNotification($userno, $enterpriseno, false);
        $arrlist = array();
        if ($m != null) {
            foreach ($m as $v) {
                $arrinfo['note'] = $v->note;
                $arrinfo['smallnote'] = $v->smallnote;
                $arrinfo['fromuser'] = $v->fromuser;
                $arrinfo['fromuserno'] = $v->fromuserno;
                $arrinfo['createtime'] = $v->createtime;
                $arrinfo['id'] = $v->id;
                $arrinfo['type'] = $v->idtype;
                $arrinfo['enterpriseno'] = $v->enterpriseno;
                $arrlist[] = $arrinfo;
            }
        }
        $json = array('returncode'=>self::SUCCESS_CODE,'content'=>$arrlist);
        return $json;
    }
    /*
     * 日程   获取日程列表
     */
    public function get_schedule_list($userno,$enterpriseno,$starttime,$endtime){
        Doo::loadClass('Today');
        $list = Today::find_myschedule($userno, $starttime, $endtime, $enterpriseno);
        $tem_arr = array();
        foreach ($list[$userno] as $k => $v) {
            foreach ($v as $k2 => $v2) {
                $list_arr['time'] = $k;
                if ($k2 == 'list') {
                    $list_arr['item'] = array();
                    foreach ($v2 as $v3) {
                        $apidata = array();
                        $apidata['todayid'] = $v3['todayid'];
                        $apidata['title'] = $v3['title'];
                        $apidata['createtime'] = $v3['createtime'];
                        $apidata['idtype'] = $v3['idtype'];
                        $apidata['id'] = $v3['id'];
                        $apidata['startdate'] = $v3['startdate'];
                        $apidata['finishdate'] = $v3['finishdate'];
                        $apidata['address'] = $v3['address'];
                        $list_arr['item'][] = $apidata;
                    }
                } else {
                    $list_arr[$k2] = $v2;
                }
            }
            $tem_arr[] = $list_arr;
        }
        $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$tem_arr);
        return $json;
    }
    /*
     * 日程  获取日程详细信息
     */
    public function get_schedule_detail($scheduleid){
        Doo::loadClass('Today');
        $res = Today::get($scheduleid);
        if ($res) {
            $result = (object) $res;
            $content = array(
                'title' => $result->title, //标题
                'starttime' => $result->startdate, //开始时间
                'endtime' => $result->finishdate, //结束时间
                'address' => $result->address, //地点
                'remark' => $result->remark, //日程详情
                'joinusers' => $result->userno, //参加人员
                'attachmentids' => $result->attachment
                ); //附件id
            $json = array('returncode'=>self::SUCCESS_CODE,'content'=>$content);
        }else{
            $json = array('returncode'=>self::NO_PERMISSION_CODE);
        }
        return $json;
    }
    /*
     * 日程  添加日程
     */
    public function edit_schedule($userno,$enterpriseno,$title,$startdate,$finishdate,$address,$readuser,$attachment){
        Doo::loadClass('Today');
        Doo::loadClass('Exterprise');
        $curuser = Exterprise::getUserInfo(array('where' => 'enterpriseno=? and userno=?', 'param' => array($enterpriseno, $userno)));
        $todayid = Today::add_schedule($title, $readuser, $enterpriseno, $startdate, $finishdate, $address, $attachment, $remark = '', $curuser);
        if($todayid){
            $json = array('returncode'=>self::SUCCESS_CODE);
        }else{
            $json = array('returncode'=>self::ERROR_CODE);
        }
        return $json;
    }
    /*
     * 日程 //获取我关注的人的日程和数量
     */
    public function get_attention_list($userno,$enterpriseno,$starttime,$endtime){
        Doo::loadClass('Today');
        Doo::loadClass('Exterprise');
        $curuser = Exterprise::getUserInfo(array('where' => 'enterpriseno=? and userno=?', 'param' => array($enterpriseno, $userno)));
        $res = Today::find_myfan_weekschedule($curuser->userno, $enterpriseno, $starttime, $endtime);
        $list = array();
        foreach ($res as $k => $v) {
            if (is_array($v)) {
                $count = 0;
                foreach ($v as $v1) {
                    $count += count($v1['list']);
                }
            } else {
                $count = -1;
            }
            $list[] = array('userid' => $k, 'count' => $count);
        }
        $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$list);
        return $json;
    }
    /*
     * 日程 编辑关注人员
     */
    public function edit_attention($users,$userno,$enterpriseno){
        Doo::loadClass('Today');
        Today::set_myfan_today_user($userno, $users, $enterpriseno);
        $json = array('returncode'=>self::SUCCESS_CODE);
        return $json;
    }
    /*
     * 日程  获取我可以建日程的人员id
     */
    public function get_arrange_permission($userno,$enterpriseno){
        Doo::loadClass('Enum');
        Doo::loadClass('UserSetting');
        $opt['where'] = 'enterpriseno=' . $enterpriseno . '  and find_in_set(' . $userno . ' ,value) and keyname=\'' . Enum::get_usersetting_key('SetMySchedule') . '\' and status>=' . Enum::getStatusType('Normal');
        $settings = UserSetting::find($opt);
        $users = array();
        foreach ($settings as $s) {
            $users[] = $s['userno'];
        }
        $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$users);
        return $json;
    }
    /*
     * 排班  获取排班列表
     */
    public function get_arrangement_list($userno,$enterpriseno,$starttime,$endtime,$pageindex,$pagesize){
        Doo::loadClass('Duty');
        $page = (($pageindex - 1) * $pagesize);
        $limit = 'limit ' . ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $where = '';
        if (!empty($starttime)) {
            $where[] = "c.date>='{$starttime}'";
        }
        if (!empty($endtime)) {
            $where[] = "c.date<='{$endtime}'";
        }
        if (!empty($where)) {
            $where = implode(' and ', $where);
            $where = 'where ' . $where;
        }
        $subsqlone = "SELECT new.*,c.name,c.date,d.frequencyname ,d.starttime,d.endtime,b.unitname from (select a.*,b.configid,b.holidayid from enterprise_duty_arrangement as a left join enterprise_duty as b on a.dutyid=b.dutyid
                          where  a.dutyuserno =$userno and a.enterpriseno =$enterpriseno) as new
                                     left join `enterprise_duty_config` as b on new.configid = b.configid
                                left join enterprise_duty_holiday as c on new.holidayid=c.holidayid
                                 left join `enterprise_frequency_settings` as d on new.dutytime = d.id #where# order by c.date asc,new.arrangeid asc #limit#";
        $subsqltwo = str_replace('#where#', $where, $subsqlone);
        $sql = str_replace('#limit#', $limit, $subsqltwo);
        $res = Doo::db()->fetchAll($sql);
        $list = array();
        foreach ($res as $v) {
            $list[] = array(
                'date' => $v['date'], 
                'typename' => $v['frequencyname'],
                'holiday' => $v['name'],
                'issign' => $v['issign'], 
                'arrangeid' => $v['arrangeid'],
                'starttime' => $v['starttime'],
                'endtime' => $v['endtime'],
                'off_issign' => $v['off_issign'],
                'way' => $v['way'], 
                'unitid' => $v['configid'], 
                'unitname' => $v['unitname']
                );
        }
        $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$list);
        return $json;
    }
    /*
     * 排班 获取排班地区
     */
    public function get_area_arrangementlist($enterpriseno){
        Doo::loadClass('Duty');
        $res = Duty::getOneUnit($enterpriseno, false);
        $list = array();
        foreach ($res as $v) {
            $list[] = array('name' => $v->unitname, 'unitid' => $v->configid);
        }
        $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$list);
        return $json;
    }
    /*
     * 排班 获得某地区排班
     */
    public function get_area_alllist($enterpriseno,$starttime,$endtime,$configid,$pageindex,$pagesize){
        $page = (($pageindex - 1) * $pagesize);
        $limit = 'limit ' . ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $where = '';
        if (!empty($starttime)) {
            $where[] = "c.date>='{$starttime}'";
        }
        if (!empty($endtime)) {
            $where[] = "c.date<='{$endtime}'";
        }
        if (!empty($where)) {
            $where = implode(' and ', $where);
            $where = 'where ' . $where;
        }
        $subsqlone = "select new.*,c.chargeuserno,c.date,d.frequencyname ,d.starttime,d.endtime,b.dutyphone from (select a.*,b.holidayid,b.configid  from enterprise_duty_arrangement as a left join enterprise_duty as b on a.dutyid=b.dutyid where a.enterpriseno =$enterpriseno and b.configid = $configid  )
           as new
                      left join `enterprise_duty_config` as b on new.configid = b.configid
           left join `enterprise_frequency_settings` as d on new.dutytime = d.id

          left join enterprise_duty_holiday as c on new.holidayid=c.holidayid  #where# order by c.date asc,new.arrangeid asc  #limit#";
        $subsqltwo = str_replace('#where#', $where, $subsqlone);
        $sql = str_replace('#limit#', $limit, $subsqltwo);

        $res = Doo::db()->fetchAll($sql);
        $list = array();
        foreach ($res as $v) {
            $init = array('usertype' => $v['dutyusertype'], //人员类型（负责人，值班人）,
                'typename' => $v['frequencyname'], //(上午班、下午班等),
                'userid' => $v['dutyuserno'],
                'leader' => $v['chargeuserno'],
                'way' => $v['way'], //（电话值班，现场值班）
                'dutyphone' => $v['dutyphone'], //值班电话
                'arrangeid' => $v['arrangeid'],
                'starttime' => $v['starttime'],
                'endtime' => $v['endtime'],
                'issign' => $v['issign'],
                'off_issign' => $v['off_issign'],
            );
            if (isset($list[$v['date']])) {
                $list[$v['date']]['item'][] = $init;
            } else {
                $list[$v['date']] = array('date' => $v['date'], 'item' => array($init));
            }
        }
        $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$list);
        return $json;
    }
    /*
     * 排班  获取节假日设置列表
     * @param $startdate 开始日期
     * @param $enddate 结束日期
     */
    public function get_holidays_list($enterpriseno,$startdate,$enddate){
        Doo::loadClass('Duty');
        $opt['where'] = 'enterpriseno=? and date between ? and ?';
        $opt['param'] = array($enterpriseno, $startdate, $enddate);
        $res = Duty::find_duty_holiday($opt);
        $list = array();
        foreach ($res as $v) {
            $list[] = array(
                'date' => $v->date,
                'holiday' => $v->name,
                'leader' => $v->chargeuserno
                    );
        }
        $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$list);
        return $json;
    }
    /*
     * 值班  值班签到
     */
    public function signin_duty($id,$type){
        $m = Doo::loadModel("EnterpriseDutyArrangement", true);
        $m->arrangeid = $id;
        if ($type == 1) {
            $m->issign = 1;
            $m->signdatetime = date("Y-m-d H:i:s");
        } else {
            $m->off_issign = 1;
            $m->off_signdatetime = date("Y-m-d H:i:s");
        }
        $m->update();
        $json = array('returncode'=>self::SUCCESS_CODE);
        return $json;
    }
    /*
     * 获取某个同事的信息，返回的信息不包含id
     */
    public function get_user_detail($userno,$enterpriseno){
        Doo::loadClass('Exterprise');
        Doo::loadClass('User');
        $user = User::getUserInfo($userno);
        $value = Exterprise::getUserEmployeeInfo(array('where' => 'userno=? and enterpriseno=?', 'param' => array($userno, $enterpriseno)));
        $employeeinfo = Exterprise::getone_employee(array('where' => 'employeeid=' . $value->employeeid));
        if ($employeeinfo != null && $employeeinfo->salt != '') {
            //清空档案表的初始密码
            Doo::db()->query("update enterprises_employee set salt='' where employeeid=$value->employeeid");
        }
        $content = array(
            "mobile" => !empty($value->mobile) ? $value->mobile : 0,
            "username" => $value->username,
            "email" => isset($value->email) ? $value->email : "",
            "departmentid" => isset($value->departmentid) ? $value->departmentid : 0,
            "department" => isset($value->departmentname) ? $value->departmentname : "",
            "positionid" => isset($value->positionid) ? $value->positionid : 0,
            'signature' => $user['user']->signature,
            "position" => isset($value->positionname) ? $value->positionname : "",
            'departmentorder'=>isset($value->departmentorder) ? $value->departmentorder : 0,
            'positionorder'=>isset($value->positionorder) ? $value->positionorder : 0,
            );
        $json = array('returncode'=>self::SUCCESS_CODE,'content'=>$content);
        return $json;
    }
    /*   
     * 附件相关  获取附件
     * @param $files 附件的id，可以是一串
     * @return ...返回附件列表内容信息
     */
    public function get_attachment_list($files) {
        if (empty($files)) {
            $json = array('returncode'=>self::NO_RECODE_CODE);
            return $json;
        }
        Doo::loadClass('Attachment');
        $att = Attachment::getAttachments($files, true);
        $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$att);
        return $json;
    }
    /*
     * 获取组件消息条数
     */
    public function get_app_msgcount($userno,$enterpriseno){
        Doo::loadClass('Today');
        Doo::loadClass('News');
        $sys_unread = $this->get_sysmsg_unread($userno, $enterpriseno);
        $pms_unread = $this->get_pms_unread($userno, $enterpriseno);
        $report_unread = $this->get_report_unread($userno, $enterpriseno);
        $plan_unread = $this->get_plan_unread($userno);
        $task_unread = $this->get_task_unread($userno, $enterpriseno);
        $news_unread = News::getUnreadNewsCounts($userno);
        $activity_unread = $this->get_activity_unread($userno, $enterpriseno);
        $meeting_unread = $this->get_meeting_unread($userno, $enterpriseno);
        $wait_unread = Today::get_waitcount($userno, $enterpriseno, date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59'));
        $p['where'] = 'enterpriseno=' . $enterpriseno . ' and userno=' . $userno;
        $today_unread = Today::get_todaycount($p);
        $v = array(
            'sys' => $sys_unread,
            'pms' => $pms_unread,
            'report' => $report_unread,
            'plan' => $plan_unread,
            'task_unstart' => $task_unread[0],
            'task_unaccept' => $task_unread[1],
            'news' => $news_unread,
            'acitvity' => $activity_unread,
            'meeting' => $meeting_unread,
            'wait' => $wait_unread,
            'today' => $today_unread,
        );
        $json = array('returncode'=>self::SUCCESS_CODE,'content'=>$v);
        return $json;
    }
    /*
     * 获取未读系统消息
     */
    private function get_sysmsg_unread($userno,$enterpriseno){
        Doo::loadClass('Exterprise');
        $employeeinfo = Exterprise::getEmployeeInfo($userno, $enterpriseno);
        Doo::loadModel('EnterpriseMsgcenter');
        $model = new EnterpriseMsgcenter();
        $opt = array(
            'select'=>'count(msgcenterid) as counts',
            'where'=>'(FIND_IN_SET(?,tousers) or tousers=?) and fromuserno!=? and enterpriseno=? and !FIND_IN_SET(?,viewusers) and createtime>=?',
            'param'=>array($userno,0,$userno,$enterpriseno,$userno,$employeeinfo->createtime),
            'desc'=>'createtime'
        );
        $list = $model->getOne($opt);
        return $list->counts; 
    }
    /*
     * 获取内部邮件未读条数
     */
    private function get_pms_unread($userno,$enterpriseno){
        Doo::loadClass('Exterprise');
        Doo::loadClass('Enum');
        Doo::loadClass('Pms');
        $option['where'] = 'userno = ? and enterpriseno = ?';
        $option['param'] = array($userno, $enterpriseno);
        $useremployee_model = Exterprise::getUserInfo($option);
        if ($useremployee_model != null) {
            $limit_time = $useremployee_model->createtime;
        }
        $where_common_email = "enterpriseno = ? and status = ? and (not FIND_IN_SET(?,deleteusers))";
        $where_unread_email = $where_common_email . " and (not FIND_IN_SET(?,viewusers)) and ((FIND_IN_SET(?,msgtoid) and msgfromid != ? ) or (msgfromid = ? and replycount != ?) or (msgfromid != ? and msgtoid = ? and createtime >= ?) or (FIND_IN_SET(?,cctousers) and msgfromid != ?))";
        $param_unread_email = array($enterpriseno, Enum::getStatusType('Normal'), $userno, $userno, $userno, $userno, $userno, 0, $userno, 0, $limit_time, $userno, $userno);
        $opt_unread['where'] = $where_unread_email;
        $opt_unread['param'] = $param_unread_email;
        $done_read_count = Pms::count_pms($opt_unread);
        return $done_read_count;
    }
    /*
     * 获取回报未读条数
     */
    private function get_report_unread($userno,$enterpriseno){
        Doo::loadModel('EnterpriseReports');
        Doo::loadClass('Enum');
        $report = new EnterpriseReports();
        $normal = Enum::getStatusType('Normal');
        $opt = array(
            'where'=>'enterpriseno=? and find_in_set(?,tousers) and !find_in_set(?,readusers) and status=?',
            'param'=>array($enterpriseno,$userno,$userno,$normal)
            );
        $count = $report->count($opt);
        return $count;
    }
    /*
     * 获取计划未读条数
     */
    private function get_plan_unread($userno){
        $option = array(
            'where'=>'FIND_IN_SET(?,tousers) and not find_in_set(?,readusers) and status=?',
            'param'=>array($userno,$userno,1101)
        );
        Doo::loadModel('EnterprisePlans');
        $plan = new EnterprisePlans();
        $count = $plan->count($option);
        return $count;
    }
    /*
     * 获取任务未读条数
     */
    private function get_task_unread($userno,$enterpriseno){
        Doo::loadClass('Enum');
        Doo::loadModel('EnterpriseTasks');
        $normal = Enum::getStatusType('Normal');
        $checked = Enum::getStatusType('Checked');
        $opt = array(
            'where'=>'status=? and enterpriseno=? and find_in_set(?,joinusers)',
            'param'=>array($normal,$enterpriseno,$userno)
        );
        $task = new EnterpriseTasks();
        $unstart_count = $task->count($opt);
        $optone = array(
            'where'=>'status>=? and enterpriseno=? and status>=? and find_in_set(?,accepteruserid)',
            'param'=>array($checked,$enterpriseno,$normal,$userno)
        );
        $accept_count = $task->count($optone);
        return array($unstart_count, $accept_count);
    }
    /*
     * 获取活动未读条数
     */
    private function get_activity_unread($userno,$enterpriseno){
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        $option = array(
            'where'=>'(userno=? or find_in_set(?,tousers) or tousers=? or find_in_set(?,joinusers)) and status=? and enterpriseno=?',
            'param'=>array($userno,$userno,'',$userno,$normal,$enterpriseno)
        );
        Doo::loadModel('EnterpriseActivity');
        $model = new EnterpriseActivity();
        $count = $model->count($option);
        return $count;
    }
    /*
     * 获取会议未读条数
     */
    private function get_meeting_unread($userno,$enterpriseno){
        $opt = array(
            'where'=>'enterpriseno=? and (find_in_set(?,joinusers) or userno=? or presenteruserno=?) and endtime>=now()',
            'param'=>array($enterpriseno,$userno,$userno,$userno)
        );
        Doo::loadModel('EnterpriseMeeting');
        $meeting = new EnterpriseMeeting();
        $count = $meeting->count($opt);
        return $count;
    }
    /*
     * 修改用户个人资料 签名
     * @param $signature 签名内容
     */
    public function edit_userinfo($userno,$signature){
        Doo::loadModel('Users');
        $user = new Users();
        $opt['where'] = "userno ={$userno}";
        $user->signature = $signature;
        $user->update($opt);
        $json = array('returncode'=>self::SUCCESS_CODE);
        return $json;
    }
    /*
     * 获取系统日志列表
     * @param $actor 操作人的userno
     * @param $type 模块类型
     * @param $dotype 具体操作
     */
    public function get_systemlog_list($enterpriseno,$pageindex,$pagesize,$actor,$starttime,$endtime,$keyword,$type,$dotype=''){
        $operater_where = $type_where = $ip_where = $time_where = $dotype_where = $keyword_where = "";
        //操作者
        if (!empty($actor))
            $operater_where = "userno=" . $actor;
        if (!empty($starttime) && !empty($endtime))
            $time_where = "createtime between '" . $starttime . "' and '" . $endtime . "'";
        if (!empty($keyword)) {
            $keyword_where = "(title like '%" . $keyword . "%' or content like '%" . $keyword . "%')";
        }
        if (!empty($type)) {
            $type_where = "idtype=" . $type;
        }
        if (!empty($dotype)) {
            $dotype_where = "idtype=" . $dotype;
        }
        $opt['where'] = array_filter(array($time_where, $type_where, $dotype_where, $operater_where, $keyword_where, $ip_where));
        $opt['where'] = implode(" and ", $opt['where']);
        if (empty($opt['where']))
            $opt['where'] = null;
        Doo::loadModel('EnterpriseEventlog');
        $log = new EnterpriseEventlog();
        $log->enterpriseno = $enterpriseno;
        $count = $log->count($opt);
        $page = (($pageindex - 1) * $pagesize);
        $opt['desc'] = 'createtime';
        $opt['limit'] = ($page < 0 ? 0 : $page) . ',' . $pagesize;
        $list = $log->find($opt);
        $msglist = array();
        foreach ($list as $v) {
            $item["enterpriseno"] = $v->enterpriseno;
            $item["name"] = $v->userno;
            $item["operate"] = $v->id;
            $item["type"] = $v->idtype;
            $item["title"] = $v->title;
            $item["content"] = $v->content;
            $item["actiontime"] = $v->createtime;
            $item["id"] = $v->eventlogid;
            $item["ip"] = $v->ip;
            $item["count"] = $count;
            $item['nowtime'] = date('Y-m-d H:i:s');
            $msglist[] = $item;
        }
        $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$msglist);
        return $json;
    }
    /*
     * 获取用户权限列表
     * @return array $res
     * state 状态
     * phonemeetingserveice 是否开通电话会议服务
     * phonemeeting 可否可拨打电话会议
     * phonenumber 电话号码
     * addfax 是否可以发传真
     * version oa版本号
     */
    public function get_permission_list($userno,$enterpriseno){
        Doo::loadClass('Permission');
        Doo::loadClass('Meeting');
        Doo::loadClass("Exterprise");
        Doo::loadClass("Enum");
        Doo::loadModel("EnterprisesEmployee");
        Doo::loadClass('Syslog');
        Doo::loadClass('Common');
        Doo::loadClass("OAClient");
        
        //同步云端手机号码到本地档案表
        $option["where"] = "userno = ? and status!=? and enterpriseno=?";
        $option["param"] = array($userno, Enum::getStatusType("Delete"), $enterpriseno);
        $userinfo = Exterprise::getUserInfo($option);
        $arr['userno'] = $userno;
        $result = json_decode(OAClient::getUserinfo($arr));
        $user = $result->user;
        $employee = new EnterprisesEmployee();
        $localemployee = $employee->getOne(array('where' => 'employeeid=' . $userinfo->employeeid));
        if ($localemployee != null && $user->mobile != '' && $user->mobile != $localemployee->mobile) {
            $localemployee->mobile = $user->mobile;
            $localemployee->updatetime = date(DATE_ISO8601);
            $localemployee->update();
        }
        
        $res = Permission::getUserPermissionList($userno, $enterpriseno);
        $is_canPhone = Meeting::is_allow_phone($enterpriseno, $userno);
        $resone = OAClient::get_conference_config($enterpriseno);
        $res1 = json_decode($resone);
        $res['state']= empty($res) ? 0 : 1;
        $res['phoneMeetingService'] = empty($res1->data) ? false : true;
        $res['phoneMeeting'] = $is_canPhone;
        $res['phoneNumber'] = $res1->data != NULL ? $res1->data[0]->openedphone : '';
        $res['addFax'] = $this->is_sendefax($userno, $enterpriseno);
        $res['version'] = OA_VERSION;
        $res['headcount'] = $this->count_employee($enterpriseno);
        OAClient::insert_login_log($enterpriseno, $userno);
        Syslog::send_syslog('', 'login', 45, $userno, '', $enterpriseno, Common::getIP(), array('form' => 1));
        $json = array('returncode'=>self::SUCCESS_CODE,'content'=>$res);
        return $json;
    }
    /*
     * (已弃用)
     * 同事录  获取同事id
     */
    public function get_colleague_id($enterpriseno,$updatetime){
        Doo::loadClass('Colleagues');
        Doo::loadClass('Enum');
        $delete = Enum::getStatusType('Delete');
        $add = $del = $update = array();
        if($updatetime){
            $lastupdatetime = date('Y-m-d H:i:s',$updatetime);
            $add_where['createtime'] = $lastupdatetime;
            $add_list = Colleagues::get_userno($add_where,'a.createtime','desc',$enterpriseno);
            if(!empty($add_list)){
                foreach($add_list['data'] as $k=>$v){
                    $add[] = $v->userno;
                }
            }
            $up_where['updatetime'] = $lastupdatetime;
            $up_list = Colleagues::get_userno($up_where,'a.createtime','desc',$enterpriseno);
            if(!empty($up_list)){
                foreach ($up_list['data'] as $k=>$v){
                    if($v->status == $delete){
                        $del[] = $v->userno;
                    }else{
                        $update[] = $v->userno;
                    }
                }
            }
        }else{
            $where['normal'] = $updatetime;
            $list = Colleagues::get_userno($where,'a.createtime','desc',$enterpriseno);
            foreach($list['data'] as $k=>$v){
                $add[] = $v->userno;
            }
        }
        $now = time();
        $json = array('returncode'=>self::SUCCESS_CODE,'add'=>$add,'del'=>$del,'update'=>$update,"nowtime"=>$now);
        return $json;
    }
    /*
     * 同事录 获取同事列表
     * @param $colleaguenos 一串id
     * @param $departmentid 部门id
     * @param $positionid  职务id
     */
    public function get_colleague_list($enterpriseno,$colleaguenos,$pageindex,$pagesize,$departmentid,$positionid){
        $where = array();
        if ($departmentid >= 0) {
            $where['departmentid'] = $departmentid;
        }
        if ($positionid >= 0) {
            $where['positionid'] = $positionid;
        }
        if($colleaguenos){
            $where['ids'] = $colleaguenos;
        }
        Doo::loadClass('Colleagues');
        Doo::loadClass('Common');
        $result = Colleagues::get_coleagues_list_for_mobile($pageindex, $pagesize, $where, 'a.createtime', 'desc', $enterpriseno);
        $list = array();
        foreach ($result as $value) {
            $list[] = array(
                "userno" => $value['userno'],
                "mobile" => !empty($value['usermobile']) ? $value['usermobile'] : 0,
                "username" => $value['username'],
                "email" => isset($value['email']) ? $value['email'] : "",
                "signature" => isset($value['signature']) ? $value['signature'] : "",
                "departmentid" => isset($value['departmentid']) ? $value['departmentid'] : 0,
                "department" => isset($value['departmentname']) ? $value['departmentname'] : "",
                'pingyin' => empty($value['cusername']) ? $value['username'] : $value['cusername'],
                "position" => isset($value['positionname']) ? $value['positionname'] : "",
                "positionorder" => isset($value['positionorder']) ? $value['positionorder'] : "",
                "departmentorder" => isset($value['departmentorder']) ? $value['departmentorder'] : "",
                );
        }
        $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$list);
        return $json;
    }
    /*
     * 同事录  获取组织架构
     */
    public function get_orgs_list($enterpriseno,$updatetime){
        Doo::loadClass("Colleagues");
        Doo::loadClass('Exterprise');
        Doo::loadClass('Enum');
        Doo::loadModel("EnterpriseDepartment");
        $model = new EnterpriseDepartment();
        $single = false;
        if($updatetime){
            $lastupdatetime = date('Y-m-d H:i:s',$updatetime);
        }else{
            $single = true;
        }
        if($updatetime){
            $update = $model->find(array('where'=>"enterpriseno=$enterpriseno and updatetime>='{$lastupdatetime}'"));
            if(count($update)>0){
                $single = true;
            }
        }
        //检测是否有删除和添加同事，有的话需要更新组织架构里面人数
        $lastupdatetime = date('Y-m-d H:i:s',$updatetime);
        $add_where['createtime'] = $lastupdatetime;
        $add_list = Colleagues::get_userno($add_where,'a.createtime','desc',$enterpriseno);
        $up_where['updatetime'] = $lastupdatetime;
        $up_list = Colleagues::get_userno($up_where,'a.createtime','desc',$enterpriseno);
        $del = array();
        $update = array();
        if(!empty($up_list)){
            foreach ($up_list['data'] as $v){
                if($v->status == Enum::getStatusType('Delete')){
                    $del[] = $v->userno;
                }else{
                    $update[] = $v->userno;
                }
            }
        }
        if(count($add_list['data'])>0||count($update)>0||count($del)>0){
            $single = true;
        }
        $opt['where'] = 'enterpriseno=? and status=?';
        $opt['asc'] = 'displayorder';
        $opt['param'] = array($enterpriseno, 1101);
        //$m = Doo::loadModel('VwEnterpriseEmployeePost', true);
        $list = $model->find($opt);
        //获取部门下人员数 
        $result = array();
        foreach ($list as $value) {
            $re['departmentid'] = $value->departmentid;
            $re['parentid'] = $value->parentid;
            $re['name'] = $value->name;
            $re['displayorder'] = empty($value->displayorder)?0:(int)$value->displayorder;
            $re['membercount'] = 0;
            $childrendeparts = Exterprise::get_subdepartment_all($value->departmentid, $enterpriseno, $list);
            $deprts = array();
            foreach ($childrendeparts as $val) {
                array_push($deprts, $val->departmentid);
            }
            array_push($deprts, $value->departmentid);
            if (count($deprts) > 0) {
                $countsql = 'SELECT COUNT(DISTINCT employeeid) AS total FROM vw_enterprise_employee_post WHERE find_in_set(departmentid,\''.implode(',', $deprts).'\')  AND enterpriseno=' . $enterpriseno . ' AND userno>0 AND status >1001 LIMIT 1';
                //$re['membercount'] = $m->count(array('where' => 'find_in_set(departmentid,?)  and enterpriseno=' . $enterpriseno . ' and userno>0 and status >?', 'param' => array(implode(',', $deprts),Enum::getStatusType('Delete'))));
                $count = Doo::db()->fetchAll($countsql);
                $re['membercount'] = $count != null ? $count[0]['total'] : 0;
            }
            $result[] = $re;
        }
        if(!$single){
            $result = array();
        }
        $now = time();
        $json = array("returncode" => self::SUCCESS_CODE, "contents" => $result,'updated'=>$single,"nowtime"=>$now);
        return $json;
    }
    /*
     * 同事录 获取职位列表
     */
    public function get_position_list($enterpriseno) {
        Doo::loadClass('Exterprise');
        $opt['where'] = 'enterpriseno=? and status=?';
        $opt['asc'] = 'displayorder';
        $opt['param'] = array($enterpriseno, 1101);
        $opt['asArray'] = true;
        Doo::loadModel("EnterprisePosition");
        $model = new EnterprisePosition();
        $list = $model->find($opt);
        $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$list);
        return $json;
    }
    /*
     * 公用删除方法 
     */
     public function del($id,$type) {
        Doo::loadClass('Enum');
        $del_model = array('plan' => 'EnterprisePlans', 'meeting' => 'EnterpriseMeeting', 'activity' => 'EnterpriseActivity', 'report' => 'EnterpriseReports', 'task' => 'EnterpriseTasks', 'new' => 'EnterpriseNews', 'pms' => 'EnterprisePms');
        $obj = Doo::loadModel($del_model[$type], true);
        $obj->{$obj->_primarykey} = $id;
        $obj->status = Enum::getStatusType('Delete');
        $obj->update();
        $json = array('returncode'=>self::SUCCESS_CODE);
        return $json;
    }
    /*
     * 统计公司员工数
     */
    private function count_employee($enterpriseno){
        $query = 'SELECT COUNT(DISTINCT a.userno) AS total FROM enterprises_employee AS g LEFT JOIN enterprise_useremployee AS a ON a.employeeid=g.employeeid';
        $where = " WHERE g.enterpriseno={$enterpriseno} AND a.userno>0 AND g.isleave=0 AND g.status>1001 LIMIT 1";
        $query .= $where;
        $c = Doo::db()->fetchAll($query);
        $t = $c[0]['total'];
        if($t != NULL){
            return $t;
        }else{
            return 0;
        }
    }
    /*
     * （已弃用）
     * 手机同事录获取用户的userno,第一次可分页获取
     */
    public function get_employee_userno($enterpriseno,$updatetime,$pageindex,$pagesize){
        $query = 'SELECT DISTINCT a.userno,g.status FROM enterprises_employee AS g LEFT JOIN enterprise_useremployee AS a ON a.employeeid=g.employeeid';
        $where = " WHERE g.enterpriseno={$enterpriseno} AND a.userno>0 AND g.isleave=0 ";
        if($updatetime > 0){
            $updatetime = date('Y-m-d H:i:s',$updatetime);
            $stradd = " AND g.createtime>='{$updatetime}' AND g.status<>1001";
            $strupdate = " AND g.updatetime>='{$updatetime}' AND g.createtime<'{$updatetime}'";
            $aquery = $query.$where.$stradd;
            $uquery = $query.$where.$strupdate;
            $alist = Doo::db()->fetchAll($aquery);
            $ulist = Doo::db()->fetchAll($uquery);
            Doo::loadClass('Enum');
            $delete = Enum::getStatusType('Delete');
            $a = $u = $d = array();
            if($alist != null){
                foreach($alist as $i){
                    $a[] = $i['userno'];
                }
            }
            if($ulist != null){
                foreach($ulist as $j){
                    if($j['status'] == $delete){
                        $d[] = $j['userno'];
                    }else{
                        $u[] = $j['userno'];
                    }
                }
            }
            $nowtime = time();
            $json = array('returncode'=>self::SUCCESS_CODE,'add'=>$a,'update'=>$u,'del'=>$d,'nowtime'=>$nowtime);
        }else{
            $strall = ' AND g.status>1001 LIMIT '.($pageindex - 1)*$pagesize.','.$pagesize;
            $query .= $where.$strall;
            $list = Doo::db()->fetchAll($query);
            $a = $u = $d = array();
            foreach($list as $item){
                $a[] = $item['userno'];
            }
            $nowtime = time();
            $json = array('returncode'=>self::SUCCESS_CODE,'add'=>$a,'update'=>$u,'del'=>$d,'nowtime'=>$nowtime);
        }
        return $json;
    }
    /*
     * 手机同事录获取用户的userno,第一次可分页获取,可以取到部门的信息
     */
    public function get_eudp_desc($enterpriseno,$updatetime,$pageindex,$pagesize,$hasname){
        $sql = "SELECT b.departmentid,a.employeeid FROM enterprise_postemployee a LEFT JOIN enterprise_post b ON a.postid=b.postid WHERE b.enterpriseno=$enterpriseno AND b.status=1101 ORDER BY a.ismajor DESC";
        $a = Doo::db()->fetchAll($sql);
        $depart = array();
        $nouse = array(0);
        foreach($a as $i){
            $depart[$i['employeeid']][] = $i['departmentid'];
        } 
        $query = 'SELECT DISTINCT a.userno,g.status,a.employeeid,g.employeename FROM enterprises_employee AS g LEFT JOIN enterprise_useremployee AS a ON a.employeeid=g.employeeid';
        $where = " WHERE g.enterpriseno={$enterpriseno} AND a.userno>0 AND g.isleave=0 ";
        if($updatetime > 0){
            $updatetime = date('Y-m-d H:i:s',$updatetime);
            $stradd = " AND g.createtime>='{$updatetime}' AND g.status<>1001";
            $strupdate = " AND g.updatetime>='{$updatetime}' AND g.createtime<'{$updatetime}'";
            $aquery = $query.$where.$stradd;
            $uquery = $query.$where.$strupdate;
            $alist = Doo::db()->fetchAll($aquery);
            $ulist = Doo::db()->fetchAll($uquery);
            Doo::loadClass('Enum');
            $delete = Enum::getStatusType('Delete');
            $a = $u = $d = '';
            if($alist != null){
                foreach($alist as $i){
                    $a[$i['userno']] = isset($depart[$i['employeeid']]) ? $depart[$i['employeeid']] : $nouse;
                }
            }
            if($ulist != null){
                foreach($ulist as $j){
                    if($j['status'] == $delete){
                        $d[$j['userno']] = isset($depart[$j['employeeid']]) ? $depart[$j['employeeid']] : $nouse;
                    }else{
                        $u[$j['userno']] = isset($depart[$j['employeeid']]) ? $depart[$j['employeeid']] : $nouse;
                    }
                }
            }
            $nowtime = time();
            $json = array('returncode'=>self::SUCCESS_CODE,'add'=>$a,'update'=>$u,'del'=>$d,'nowtime'=>$nowtime);
        }else{
            $strall = ' AND g.status>1001 LIMIT '.($pageindex - 1)*$pagesize.','.$pagesize;
            $query .= $where.$strall;
            $list = Doo::db()->fetchAll($query);
            $a = $n = array();
            foreach($list as $item){
                $a[$item['userno']] = isset($depart[$item['employeeid']]) ? $depart[$item['employeeid']] : $nouse;
                $n[$item['userno']] = $item['employeename'];
            }
            $nowtime = time();
            $json = array('returncode'=>self::SUCCESS_CODE,'add'=>$a,'update'=>'','del'=>'','nowtime'=>$nowtime);
            if(!empty($hasname)){
                $json['username'] = $n;   //iphone同时返回名字
            }
        }
        return $json;
    }
    /*
     * 获取人员的部门职位信息 可能是多部门 多职位
     */
    public function get_employee_postdetail($userno,$enterpriseno){
        $m = Doo::loadModel('VwEnterpriseEmployeePost',true);
        $opt = array(
            'select'=>'enterpriseno,departmentid,positionid,departmentname,positionname,employeename',
            'where'=>'userno=? AND enterpriseno=?',
            'param'=>array($userno,$enterpriseno),
            'asArray'=>true
        );
        $items = $m->find($opt);
        if($items != null){
            $json = array('returncode'=>self::SUCCESS_CODE,'contents'=>$items);
        }else{
            $json = array('returncode'=>self::ERROR_CODE);
        }
        return $json;
    }
}

?>
