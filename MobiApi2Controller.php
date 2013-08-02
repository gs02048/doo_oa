<?php
/*
 * 
 */
error_reporting(E_ALL);

class MobiApi2Controller extends DooController {
    private $json = array("error" => false, "message" => '', "data" => array());
    private $time;
    private $request;
    
    public function __construct() {
        Doo::loadClass("UserCommon");
        Doo::loadClass('MobiApi');
        $this->time = time();
        header("Content-type:application/json");
        header("Content-type: application/json");
    }
    /*
     * 初始化请求数据
     * 参照ucenter加密方式
     */
    private function init_requestdata($getagent = '') {
        $data = $this->getgpc('input', 'R');
        $codetype = $this->getgpc('ct', 'R');
        if ($data) {
            if ($codetype && $codetype == 'b64') {
                $data = base64_decode(str_replace(" ", "+", $data));
            } else {
                $data = UserCommon::uc_authcode($data, 'DECODE', OA_KEY);
            }
            parse_str($data, $this->request);
            $this->request = $this->daddslashes($this->request, 1, TRUE);
            $agent = $getagent ? $getagent : $this->request['agent'];
            if (($getagent && $getagent != $this->request['agent']) || (!$getagent && md5($_SERVER['HTTP_USER_AGENT']) != $agent)) {
                //exit('Access denied for agent changed');
            } elseif ($this->time - $this->request['time'] > 3600) {
                $this->json["error"] = true;
                $this->json["data"] = 'Authorization has expired';
                echo json_encode($this->json);
                exit;
            }
        }
        if (empty($this->request)) {
            $this->json["error"] = true;
            $this->json["data"] = 'Invalid request data';
            echo json_encode($this->json);
            exit;
        }
    }
    /*
     * 
     */
    private function getgpc($k, $var = 'R') {
        switch ($var) {
            case 'G': $var = &$_GET;
                break;
            case 'P': $var = &$_POST;
                break;
            case 'C': $var = &$_COOKIE;
                break;
            case 'R': $var = &$_REQUEST;
                break;
        }
        return isset($var[$k]) ? $var[$k] : NULL;
    }
    /*
     * 
     */
    private function daddslashes(&$string, $force = 0, $strip = FALSE) {
        if (!get_magic_quotes_gpc() || $force) {
            if (is_array($string)) {
                foreach ($string as $key => $val) {
                    $string[$key] = $this->daddslashes($val, $force, $strip);
                }
            } else {
                $string = addslashes($strip ? stripslashes($string) : $string);
            }
        }
        return $string;
    }
    private function check_user(){
        if(empty($this->request['userno']) || empty($this->request['enterpriseno'])){
            $json = array('returncode'=>MobiApi::NO_PERMISSION_CODE);
            $result = json_encode($json);
            $this->write_json($result);
            exit;
        }
    }
    /*
     * 输出json结果
     */
    private function write_json($result) {
        $callback = $this->getgpc('callback', 'R');
        if (isset($callback)) {
            echo $callback . '(' . $result . ');';
        } else {
            echo $result;
        }
        //打印sql
        if(isset($_GET['oa_mobile_debug']) && $_GET['oa_mobile_debug'] == 'sb'){
            print_r($this->request);
            print_r(Doo::db()->show_sql());
        }
        exit;
    }
    //=========================== 新闻公告 =====================
    public function news_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case "addnews":
                $this->add_news();   //添加公告
                break;
            case 'mypublish':        
                $this->get_mypublish_list();  //我发布的公告
                break;
            case "newslist":
                $this->get_news_list();  //公告列表
                break;
            case "getnewsinfo":
                $this->get_news_info();  //公告详情
                break;
            case "delnews":
                $this->del_news();  //删除公告
                break;
        }
    }
    //=========================== 汇报 =====================
    public function report_ajax(){
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action){
            case "addreport":
                $this->add_report();   //添加汇报
                break;
            case "getreportinfo":
                $this->get_report_info();  //汇报详情
                break;
            case "getreportlist":
                $this->get_report_list();  //我的汇报
                break;
            case "getreadreportlist": //批阅汇报列表
                $this->get_todo_report_list();
                break;
            case "readreport"://已批阅
                $this->read_report();
                break;
            case "getallreportlist": //全部汇报
                $this->all_report_list();
                break;
        }
    }
    //=================================开会===================
    public function meeting_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case "addmeeting":
                $this->add_meeting();       //添加会议
                break;
            case "meetinglist":
                $this->get_meeting_list();  //会议列表
                break;
            case "getmeetinginfo":
                $this->get_meeting_info();  //会议详情
                break;
            case "acceptmeeting":
                $this->accept_meeting();  //参加会议
                break;
            case "rejectmeeting":
                $this->reject_meeting();  //拒绝参加会议
                break;
            case 'deletemeeting':
                $this->delete_meeting();   //删除会议
                break;
            case "meetingtask":
                $this->find_meeting_task();  //查找会议的任务
                break;
            case "meetingaddresslist":
                $this->meeting_address_list();  //会议地址列表
                break;
            case "getteleconferencelist":
                $this->phone_meeting_list();  //电话会议列表
                break;
            case 'addphonemeeting':
                $this->add_phone_meeting();   //添加电话会议
                break;
            case 'getmeetingstate':
                $this->get_meeting_state();  //获取电话会议状态
                break;
            case 'getphonemeetinglist':
                $this->phone_meeting_list();  //电话会议列表
                break;
            case 'getphonemeetinginfo': 
                $this->get_phone_meeting_info();  //电话会议详情
                break;
            case 'setmeetingstatus':
                $this->set_meeting_status();   //设置电话会议详情
                break;
            case 'intivemeeting':
                $this->intive_meeting();    //邀请电话会议
                break;
            case 'endphonemeeting':
                $this->end_phone_meeting();   //结束电话会议
                break;
        }
    }
    //=================================计划===================
    public function plan_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case "getplanlist"://获取我的计划列表
                $this->get_plan_list();
                break;
            case "getplaninfo"://获取计划详细
                $this->get_plan_info();
                break;
            case "addplan"://写计划
                $this->add_plan();
                break;
            case "readplan"://已批阅
                $this->read_plan();
                break;
            case "getreadplanlist": //批阅计划列表
                $this->get_todo_plan_list();
                break;
            case "getallplanlist": //全部计划
                $this->get_all_plan_list();
                break;
            case 'del':
                $this->del_plan();
                break;
        }
    }
    //==============================申请审批===================
    /*
     * 申请审批的东西
     * priority
      审批状态 status （1:待审批  2:审批中   3:已通过  4:不通过 5:已撤销）
      审批名字 name
      创建时间 createtime
     *
     */
    public function process_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case "mycaselist":
                $this->my_apply_list();  //我的申请列表
                break;
            case "todocaselist":
                $this->get_todocase_list();   //我的审批的列表
                break;
            case 'getcaseform':
                $this->get_case_list();   //获取我能提交的申请单
                break;
            case 'addcaseinfo':
                $this->add_case();   //新建审批申请
                break;
            case 'getformprocess':
                $this->get_form_process();     //获取审批表单流程
                break;
            case 'getcaseinfo':
                $this->get_process_detail();   //获取审批详情
                break;
            case 'handelcase':
                $this->handel_case();   //进行审批操作
                break;
            case 'reminderprocess':
                $this->reminder_process();  //审批催办
                break;
            case 'getrollback':
                $this->get_rollback_step();      //获取审批回退步骤
                break;
            case 'tableinfo':
                $this->get_table_info();           //获取审批单内的表格控件明细
                break;
            case 'checkform':                      //检测表单是否可用
                $this->check_form_status();
                break;
        }
    }
    //==============================活动===================
    public function activity_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case "myactivelist":
                $this->my_activity_list();  //获取我的活动列表
                break;
            case "activeinfo":
                $this->activity_info();  //活动详情
                break;
            case "activeinfo":
                $this->get_comment_list();  //获取活动的评论列表
                break;
            case "joinactivity":
                $this->join_activity();  //加入活动
                break;
            case 'addactivity':
                $this->add_activity();   //添加活动
                break;
            case 'delactivity':
                $this->del_activity();   //删除活动
                break;
        }
    }
    //==============================文件柜===================
    public function file_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case 'getshortcutlist': 
                $this->get_shortcut_list(); //文件柜快捷方式
                break;
            case 'getdirfile':
                $this->get_dirfile();  //获取目录下的文件
                break;
            case 'createdir':
                $this->create_dir();  //创建文件夹
                break;
            case 'renamefile':
                $this->rename_file();  //重命名文件夹或文件
                break;
            case 'delfile':
                $this->del_file();  //删除文件
                break;
            case 'sharefile':
                $this->share_file();   //共享文件 
               break;
           case 'canclesharefile':
               $this->cancle_share_file();  //取消共享 
               break;
           case 'getsharefileuser':
               $this->get_sharefile_user();  //获取共享文件的共享人员 
               break;
           case 'uploadfile':
               $this->upload_file();    //添加文件到文件柜
               break;
           case 'savedocpropertypanelinfo':
               $this->save_docpropertypanel_info();  //设置文件或者文件夹的属性
               break;
           case 'colleagueshare':
               $this->get_colleague_sharedoc();  //获取同事共享的文件
               break;
           case 'checkhashcode':    //手机断点上传 检查文件分块的上传情况
               $this->check_code();
               break;
           case 'addhashcode':    
               $this->add_code();  //手机断点上传 添加文件记录
               break;
           case 'getdocproperty':
               $this->get_doc_property();  //获取目标文件的属性
               break;
           case 'updateshortcut':
               $this->update_shortcut();   //修改或删除快捷方式
               break;
           case 'sharelist':
               $this->get_share_list();    //获取文件/文件夹分享列表
               break;
           case 'editdocshare':
               $this->edit_doc_share();      //设置文件柜doc共享
               break;
           case 'cancleshare':
               $this->cancle_share_doc();     //取消文件柜doc共享
               break;
        }
    }
    //==============================客户===================
    public function customer_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case 'getcustomerlist': 
                $this->get_customer_list();  //获取我的客户或公司客户列表
                break;
            case 'getcustomerinfo':
                $this->get_customer_info();  //获取客户详细信息
                break;
            case 'getcustomertype':
                $this->get_customer_type();  //获取客户类型
                break;
            case 'addcustomer':
                $this->add_customer();    //添加客户
                break;
            case 'delcustomer':
                $this->del_customer();   //删除客户
                break;
        }
    }
    //==============================服务拜访===================
    public function visit_ajax(){
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case 'getmyvisitlist':              //获取我的拜访列表
                $this->get_myvisit_list();
                break;
            case 'getmyremarklist':
                $this->get_myremark_list();   //服务拜访我的批阅
                break;
            case 'getotherrecorderlist':
                $this->get_otherrecorder_list();   //拜访中同事记录列表
                break;
            case 'addvisit':
                $this->add_visit_service();   //新建服务或拜访
                break;
            case 'getvisitinfo':
                $this->get_visit_info();    //服务拜访获取详情
                break;
            case 'getvisittypes':
                $this->get_visit_types();  //服务拜访类别
                break;
            case 'setisread':
                $this->set_isread();  //设置已读
                break;
            case 'delvisit':
                $this->delete_visit();       //服务拜访删除功能
                break;
            case 'customformitem':
                $this->get_customform_item();       //获取自定义表单元素
                break;
        }
    }
    /**
     * 处理站内信相关ajax请求
     */
    public function pms_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case "sendpms":
                $this->add_pms();   //发送内部邮件
                break;
            case "receivepmslist":
                $this->receive_pms_list();   //收件箱
                break;
            case "sendpmslist":
                $this->send_pms_list();   //发件箱列表
                break;
            case "pmsinfo":
                $this->get_pms_info();   //内部邮件详情
                break;
            case "postvote":
                $this->post_vote();    //内部邮件投票
                break;
            case "postcomment":        //内部邮件回复
                $this->post_comment();  
                break;
            case "getreplaylist":
                $this->get_replay_list();   //获取邮件的回复列表
                break;
            case 'delpms':
                $this->del_pms();         //删除内部邮件
                break;
        }
    }
     /**
     * 处理评论相关ajax请求
     */
    public function comment_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case "getcommentlist":
                $this->get_comment_list();    //获取某条记录的评论列表
                break;
            case "addcomment":
                $this->add_comment();         //添加评论
                break;
        }
    }
    /*
     * 处理手机注册的
     */
    public function reg_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case 'getapplymessage':   
                $this->get_apply_message();  //获取申请加入公司的信息
                break;
            case 'agreejoin':
                $this->agree_join();  //同意加入公司（别人申请加入）
                break;
            case 'refusejoin':
                $this->refuse_join();  //拒绝加入公司（别人申请加入）
                break;
            case 'helpjoin':
                $this->help_join();  //帮助注册
                break;
        }
    }
    /*
     * 新建传真的东西
     * 流程如下，使用第三方接口，soap webservice 使用xml传送，cron实行执行任务
     * 不过对soap还是有些不懂，特别是他的语法，wmxl
     */
    public function fax_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case "faxreceivelist":
                $this->get_fax_list();   //传真收件箱列表
                break;
            case "faxsentlist":
                $this->fax_sent_list();   //传真发件箱列表
                break;
            case "faxadd":
                $this->add_fax();   //添加传真
                break;
            case "getfaxdetail":
                $this->get_fax_info();  //获取传真详情
                break;
        }
    }
    /**
     * 处理项目任务相关ajax请求
     */
    public function project_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case "newtask":
                $this->create_task();    //发布、编辑任务
                break;
            case "starttask":
                $this->start_task();   //开始执行任务
                break;
            case "settaskpercentage":
                $this->set_task_percentage();  //设置任务进度
                break;
            case "mytasklist":
                $this->get_my_task_list();   //获取我的任务列表
                break;
            case "accepttasklist":      
                $this->get_accept_task_list();  //获取我验收的任务列表
                break;
            case "accepttask":
                $this->accept_task();      //验收任务
                break;
            case "alltask":
                $this->get_all_task();    //全部任务
                break;
            case "gettaskinfo":
                $this->get_task_info();   //获取任务详情
                break;
            case "requestacceptance":
                $this->request_accepttask();   //申请验收任务
                break;
            case "deletetask":
                $this->delete_task();       //删除任务
                break;
        }
    }
    /*
     * 系统消息相关接口
     */
    public function msg_ajax(){
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case 'getmsglist':
                $this->get_msg_list();    //获取系统消息列表
                break;
            case 'getlastnotification':
                $this->get_last_notification();   //获取最后一条推送
                break;
        }
    }
    /*
     * 日程、排班相关接口
     */
    public function schedule_ajax() {
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case "getschedulelist":
                $this->get_schedule_list();     //获取日程列表
                break;
            case 'arrangementlist':
                $this->get_arrangement_list();  //获取排班列表
                break;
            case 'getarrangementlistbyunit':
                $this->get_arrangementlist_byunit();  //获取排班地区
                break;
            case 'getalllistbyunit':
                $this->get_alllist_byunit();   //获得某地区排班
                break;
            case 'getscheduleinfo':
                $this->get_schedule_info();   //获取日程详细
                break;
            case 'getholidaylist':
                $this->get_holidays_list(); //获取节假日设置列表
                break;
            case 'dutysign':
                $this->sign_duty();   //值班签到
                break;
            case 'addschedule':
                $this->add_schedule();   //;添加日程
                break;
            case 'getattention':      
                $this->get_attention();  //获取我关注的人的日程数量
                break;
            case 'addattention':
                $this->add_attention();  //编辑关注人员
                break;
            case 'getarrangepermission':
                $this->get_arrange_permission();   //获取我可以建日程的人员id
                break;
        }
    }
    /*
     * 零散接口与系统相关
     */
    public function sys_ajax(){
        $this->init_requestdata();
        $action = $this->request["action"];
        switch ($action) {
            case 'getuserinfo':
                $this->get_user_info();  //获取某个同事的信息，返回的信息不包含id
                break;
            case 'getattachments':
                $this->get_attachments();  //根据附件ID获取附件信息
                break;
            case 'getlastsetinfo':
                $this->get_last_setinfo();   //获取组件消息条数
                break;
            case 'updateuser':
                $this->update_user();   //修改个人签名
                break;
            case 'getsyslog':
                $this->get_syslog_list();  //获取系统日志列表
                break;
            case 'getpermission':
                $this->get_permission();   //获取用户的权限列表
                break;
            case 'getcolleagueid':
                $this->get_colleague();   //同事录  获取同事id
                break;
            case 'getcolleagues':
                $this->get_colleague_list();  //同事录 获取同事列表
                break;
            case 'getorglist':
                $this->get_organization_list();   //同事录  获取组织架构
                break;
            case 'getposlist':
                $this->get_position_list(); //同事录 获取职位列表
                break;
            case 'del':
                $this->comm_del();   //公共删除方法
                break;
            case 'employeepostdetail':              //获取人员的部门职位信息 可能是多部门 多职位
                $this->get_employee_detail();
                break;
        }
    }
    /*
     * 公告  新增或者更新公告
     */
    private function add_news(){
        if (isset($this->request['method']) && $this->request['method'] == 'post') {
            $this->request = $_POST;
        }
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $receiptusers = empty($this->request['readuser']) ? '' : $this->request['readuser'];
        $content = $this->request['content'];
        $title = $this->request['title'];
        $newscategoryid = empty($this->request['type']) ? 1 : $this->request['type'];
        $enddate = $this->request['endtime'];
        $readusers = empty($this->request['readuser']) ? '' : $this->request['readuser'];
        $istop = $this->request['topshow'];
        $cancomment = $this->request['cancomment'];
        $attachmentids = isset($this->request['attachmentid']) ? $this->request['attachmentid'] : '';
        $dis = isset($this->request['dis']) ? $this->request['dis'] : 1101;
        $pubcom = isset($this->request['pubcom']) ? $this->request['pubcom'] : 1101;
        $newsid = empty($this->request['id']) ? 0 : $this->request['id'];
       
        $mobiapi = new MobiApi();
        $json = $mobiapi->edit_news($newsid, $userno, $enterpriseno, $content, $title, $receiptusers, $newscategoryid, $enddate, $readusers, $istop, $cancomment, $attachmentids,$dis,$pubcom);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 公告 获取公告列表
     */
    private function get_news_list(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $pageindex = !empty($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = !empty($this->request["pagesize"]) ? $this->request["pagesize"] : 20;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        $search_type = !empty($this->request["status"]) ? $this->request["status"] : '';
        
        $mobi = new MobiApi();
        $json = $mobi->get_news_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime, $search_type);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 公告  获取我发布的公告列表
     */
    private function get_mypublish_list(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $pageindex = !empty($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = !empty($this->request["pagesize"]) ? $this->request["pagesize"] : 20;
        $lasttime = !empty($this->request["updatetime"]) ? $this->request["updatetime"] : -1;
        $type = !empty($this->request["type"]) ? $this->request["type"] : '';
        
        $mobi = new MobiApi();
        $json = $mobi->get_mypublish_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime, $type);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 公告 获取公告详情
     */
    private function get_news_info(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $newsid = isset($this->request['newsid']) ? $this->request['newsid'] : 0;
        
        $mobi = new MobiApi();
        $json = $mobi->get_news_detail($newsid,$userno,$enterpriseno);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 公告  删除公告
     */
    private function del_news(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $newsid = isset($this->request['newsid']) ? $this->request['newsid'] : 0;
        $mobi = new MobiApi();
        $json = $mobi->delete_news($newsid,$userno,$enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 汇报 写汇报
     */
    private function add_report(){
        if (isset($this->request['method']) && $this->request['method'] == 'post') {
            $this->request = $_POST;
        }
        $this->check_user();
        $type = $this->request['type'];
        $userno = $this->request["userno"];
        $enterpriseno = $this->request['enterpriseno'];
        $title = $this->request["title"];
        $starttime = $this->request['starttime'];
        $endtime = $this->request['endtime'];
        $tousers = $this->request["tousers"];
        $content = $this->request["content"];
        $cctousers = $this->request["cctousers"];
        $item = isset($this->request['item']) ? $this->request['item'] : false;
        $attachmentid = isset($this->request["attachmentid"]) ? $this->request['attachmentid'] : '';
        $reportid = isset($this->request['id']) ? $this->request['id'] : '';
        
        $mobi = new MobiApi();
        $json = $mobi->edit_report($reportid, $userno, $enterpriseno, $title, $content, $type, $starttime, $endtime, $tousers, $cctousers, $attachmentid,$item);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 汇报 获取汇报详情
     */
    private function get_report_info(){
        $this->check_user();
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $reportid = isset($this->request['reportid']) ? $this->request['reportid'] : 0;
        
        $mobi = new MobiApi();
        $json = $mobi->get_report_detail($reportid, $enterpriseno);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 汇报 获取汇报列表
     */
    private function get_report_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 20;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;

        $mobi = new MobiApi();
        $json = $mobi->get_report_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);

        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 汇报 批阅汇报列表
     */
    private function get_todo_report_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        
        $mobi = new MobiApi();
        $json = $mobi->get_todo_report_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 汇报 批阅汇报 
     */
    private function read_report(){
        $this->check_user();
        $reportid = $this->request['reportid'];
        $userno = $this->request["userno"];
        $enterpriseno = $this->request['enterpriseno'];

        $mobi = new MobiApi();
        $json = $mobi->read_report($reportid, $userno, $enterpriseno);

        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 汇报 获取全部汇报
     */
    private function all_report_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 100;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;

        $mobi = new MobiApi();
        $json = $mobi->get_all_report_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);

        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 会议  添加会议
     */
    private function add_meeting(){
        if (isset($this->request['method']) && $this->request['method'] == 'post') {
            $this->request = $_POST;
        }
        $this->check_user();
        $meetingid = isset($this->request['id']) ? $this->request['id'] : 0;
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $hostid = $this->request["hostid"];
        $meetingname = $this->request["meetingname"];
        $meetingcontent = $this->request["meetingcontent"];
        $meetingdate = $this->request["meetingdate"];
        $starttime = $this->request["starttime"];
        $endtime = $this->request["endtime"];
        $meetingaddress = $this->request["meetingaddress"];
        $joiners = $this->request["joiners"];
        $attachmentid = $this->request["attachmentid"];
        $viewusers = $this->request["viewusers"];
        
        $mobi = new MobiApi();
        $json = $mobi->edit_meeting($meetingid, $userno, $enterpriseno, $hostid, $meetingname, $meetingcontent, $meetingdate, $starttime, $endtime, $meetingaddress, $joiners, $attachmentid, $viewusers);

        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 会议 获取会议列表  type为all时为全部列表 否则为我的列表
     */
    private function get_meeting_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        $type = !empty($this->request['type']) ? $this->request['type'] : '';
        $status = !empty($this->request["status"]) ? $this->request["status"] : 'all';
        
        $mobi = new MobiApi();
        $json = $mobi->get_meeting_list($userno, $enterpriseno, $pageindex, $pagesize, $type, $status, $lasttime);

        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 会议 获取会议详情
     */
    private function get_meeting_info(){
        $this->check_user();
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $meetingid = isset($this->request['meetingid']) ? $this->request['meetingid'] : 0;
        $mobi = new MobiApi();
        $json = $mobi->get_meeting_detail($enterpriseno, $meetingid);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 会议 确认加入会议
     */
    private function accept_meeting(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $meetingid = isset($this->request['meetingid']) ? $this->request['meetingid'] : 0;
        
        $mobi = new MobiApi();
        $json = $mobi->accept_meeting($userno, $meetingid);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 会议 拒绝参与会议
     */
    private function reject_meeting(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $meetingid = isset($this->request['meetingid']) ? $this->request['meetingid'] : 0;
        $reason = $this->request['reason'];
        
        $mobi = new MobiApi();
        $json = $mobi->reject_meeting($userno, $meetingid,$reason);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 会议  删除会议
     */
    private function delete_meeting(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $meetingid = isset($this->request['meetingid']) ? $this->request['meetingid'] : 0;
        Doo::loadClass('Meeting');
        Meeting::delete_meeting($meetingid, $userno, $enterpriseno);
        $json = array('returncode'=>MobiApi::SUCCESS_CODE);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 会议 获取会议的任务列表
     */
    private function find_meeting_task(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $meetingid = isset($this->request['meetingid']) ? $this->request['meetingid'] : 0;

        $mobi = new MobiApi();
        $json = $mobi->find_task_by_meetingid($userno, $enterpriseno, $meetingid);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 会议 获取会议地址列表
     */
    private function meeting_address_list(){
        $this->check_user();
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;

        Doo::loadClass("Meeting");
        $res = Meeting::get_all_meetingroom_info(array('select' => 'roomname', 'where' => "enterpriseno={$enterpriseno}"));
        $list = array();
        foreach ($res as $value) {
            $list[] = array("roomname" => $value->roomname);
        }
        $result = json_encode(array("returncode" => MobiApi::SUCCESS_CODE, "contents" => $list));
        $this->write_json($result);
    }
    /*
     * 电话会议 获取电话会议列表
     */
    private function phone_meeting_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;

        $mobi = new MobiApi();
        $json = $mobi->get_phone_meeting_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 电话会议 添加电话会议
     */
    private function add_phone_meeting(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $joinuser = isset($this->request["joinuser"]) ? $this->request["joinuser"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $phones = isset($this->request['phones']) ? $this->request['phones'] : 0;
        $title = isset($this->request['title']) ? $this->request['title'] : '';
        
        $mobi = new MobiApi();
        $json = $mobi->edit_phone_meeting($userno, $enterpriseno, $joinuser, $phones, $title);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 会议  获取会议状态
     */
    private function get_meeting_state(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $mid = isset($this->request["mid"]) ? $this->request["mid"] : 0;
        $conferenceid = isset($this->request["conferenceid"]) ? $this->request["conferenceid"] : 0;

        Doo::loadClass("Meeting");
        $res1 = Meeting::get_conference_state($enterpriseno, $userno, $mid, $conferenceid);
        $state = array();
        foreach ($res1 ['list'] as $v) {
            $state[] = array(
                'phone' => $v['participant'],
                'status' => $v['status']
                );
        }
        $result = json_encode(array('success' => 1, 'data' => $state));
        $this->write_json($result);
    }
    /*
     * 电话会议 电话会议详情
     */
    private function get_phone_meeting_info(){
        $this->check_user();
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $meetingid = isset($this->request['meetingid']) ? $this->request['meetingid'] : 0;
        $conferenceid = isset($this->request['conferenceid']) ? $this->request['conferenceid'] : 0;

        $mobi = new MobiApi();
        $json = $mobi->get_phone_meeting_detail($enterpriseno, $meetingid, $conferenceid);

        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 电话会议  设置电话会议状态
     */
    private function set_meeting_status(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $phones = isset($this->request['phones']) ? $this->request['phones'] : 0;
        $conferenceid = isset($this->request['conferenceid']) ? $this->request['conferenceid'] : 0;
        $type = isset($this->request['type']) ? $this->request['type'] : 0;
        Doo::loadClass("OAClient");
        if ($type == "silence") {
            $res = OAClient::mute_participants($enterpriseno, $userno, $conferenceid, $phones);
        } elseif ($type == "resound") {
            $res = OAClient::restore_participants($enterpriseno, $userno, $conferenceid, $phones);
        } else {
            $res = OAClient::disconnect_participants($enterpriseno, $userno, $conferenceid, $phones);
        }

        $json = array('success'=>1,'message'=>MobiApi::SUCCESS_CODE);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 电话会议 邀请电话会议
     */
    private function intive_meeting(){
        $conferenceid = $this->request['conferenceid'];
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $phones = $this->request['phones'];
        Doo::loadClass("OAClient");
        $result = OAClient::invite_participants($enterpriseno, $userno, $conferenceid, $phones);
        $this->write_json($result);
    }
    /*
     * 电话会议 结束电话会议
     */
    private function end_phone_meeting(){
        $conferenceid = isset($this->request["conferenceid"]) ? $this->request["conferenceid"] : 0;
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        Doo::loadClass("OAClient");
        $result = OAClient::end_conference($enterpriseno, $userno, $conferenceid);
        $this->write_json($result);
    }
    /*
     * 计划 获取我的计划列表
     */
    private function get_plan_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;

        $mobi = new MobiApi();
        $json = $mobi->get_plan_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 计划 获取计划详细
     */
    private function get_plan_info(){
        $this->check_user();
        $planid = isset($this->request['planid']) ? $this->request['planid'] : 0;

        $mobi = new MobiApi();
        $json = $mobi->get_plan_detail($planid);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 计划 写计划
     */
    private function add_plan(){
        if (isset($this->request['method']) && $this->request['method'] == 'post') {
            $this->request = $_POST;
        }
        $this->check_user();
        $type = $this->request['type'];
        $userno = $this->request["userno"];
        $enterpriseno = $this->request['enterpriseno'];
        $title = $this->request["title"];
        $starttime = $this->request['starttime'];
        $endtime = $this->request['endtime'];
        $tousers = $this->request["tousers"];
        $content = $this->request["content"];
        $item = isset($this->request['item']) ? $this->request['item'] : FALSE;
        $planid = isset($this->request["id"]) ? $this->request["id"] : '';
        $attachmentid = isset($this->request["attachmentid"]) ? $this->request["attachmentid"] : '';
        $cctousers = isset($this->request["cctousers"]) ? $this->request["cctousers"] : $this->request["cctousers"];
        $mobi = new MobiApi();
        $json = $mobi->edit_plan($planid, $userno, $enterpriseno, $title, $content, $type, $starttime, $endtime, $tousers, $cctousers, $attachmentid,$item);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 计划 已批阅
     */
    private function read_plan(){
        $this->check_user();
        $planid = $this->request['planid'];
        $userno = $this->request["userno"];
        $enterpriseno = $this->request['enterpriseno'];

        $mobi = new MobiApi();
        $json = $mobi->read_plan($planid, $userno, $enterpriseno);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 计划 批阅计划列表
     */
    private function get_todo_plan_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        
        $mobi = new MobiApi();
        $json = $mobi->get_review_plan_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 计划 全部计划
     */
    private function get_all_plan_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;

        $mobi = new MobiApi();
        $json = $mobi->get_allplan_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);

        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 计划/汇报  删除计划或汇报
     */
    private function del_plan(){
        $this->check_user();
        $type = isset($this->request['type']) ? $this->request['type'] : 0;
        $id = isset($this->request['id']) ? $this->request['id'] : 0;
        $mobi = new MobiApi();
        $json = $mobi->delete_plan($type,$id);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 申请审批 我的申请列表
     */
    private function my_apply_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        
        $mobi = new MobiApi();
        $json = $mobi->my_apply_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 申请审批   待我审批的列表
     */
    private function get_todocase_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        
        $mobi = new MobiApi();
        $json = $mobi->get_docase_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 申请审批 获取申请审批单详情
     */
    private function get_process_detail(){
        $this->check_user();
        $enterpriseno = $this->request['enterpriseno'];
        $userno = $this->request['userno'];
        $caseid = $this->request['caseid'];
        $mobi = new MobiApi();
        $json = $mobi->get_case_detail($userno, $enterpriseno, $caseid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     *申请审批 获取我能提交的申请单
     */
    private function get_case_list(){
        $this->check_user();
        $enterpriseno = $this->request['enterpriseno'];
        $userno = $this->request['userno'];
        $mobi = new MobiApi();
        $json = $mobi->get_case_list($userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 申请审批  新建审批申请
     */
    private function add_case(){
        if (isset($this->request['method']) && $this->request['method'] == 'post') {
            $this->request = $_POST;
        }
        $this->check_user();
        $enterpriseno = $this->request['enterpriseno'];
        $userno = $this->request['userno'];
        $name = $this->request['name'];
        $processid = $this->request['processid'];
        $dynaformid = $this->request['dynaformid'];
        $priority = $this->request['priority'];
        $nextStepid = isset($this->request['nextstepid']) ? $this->request['nextstepid'] : 0;
        $approvers = $this->request['approvers'];
        $remark = isset($this->request['suggest']) ? $this->request['suggest'] : '';
        $pass = isset($this->request['turn']) ? $this->request['turn'] : 1; 
        $sign = isset($this->request['sign']) ? $this->request['sign'] : ''; //签名附件id
        $attachmentid = isset($this->request['attachment']) ? $this->request['attachment'] : 0; //附件id
        $mode = !empty($this->request['mode']) ? $this->request['mode'] : 1;
        $form_abstract = isset($this->request['abstract']) ? $this->request['abstract'] : 0;
        $applicant_data = isset($this->request['applicantdata']) ? $this->request['applicantdata'] : '';
        $param = $this->request;
        $mobi = new MobiApi();
        $json = $mobi->edit_case($userno, $enterpriseno, $name, $processid, $dynaformid, $priority, $nextStepid, $approvers, $remark, $pass, $sign, $attachmentid, $mode,$param,$form_abstract,$applicant_data);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 申请审批  获取审批表单流程
     */
    private function get_form_process(){
        $this->check_user();
        $enterpriseno = $this->request['enterpriseno'];
        $userno = $this->request['userno'];
        $formid = $this->request['formid'];
        $mobi = new MobiApi();
        $json = $mobi->get_form_process($userno, $enterpriseno, $formid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 申请审批  进行审批操作
     */
    private function handel_case(){
        $this->check_user();
        $caseid = $this->request["caseid"];
        $state = $this->request["type"];
        $remark = $this->request["remark"];
        $enterpriseno = isset($this->request['enterpriseno']) ? $this->request['enterpriseno'] : 0;
        $userno = isset($this->request['userno']) ? $this->request['userno'] : 0;
        $pass = isset($this->request['turn']) ? $this->request['turn'] : 1; 
        $sign = isset($this->request['sign']) ? $this->request['sign'] : ''; //签名附件id
        $attachmentid = isset($this->request['attachment']) ? $this->request['attachment'] : '';
        $rollbackid = isset($this->request['backid']) ? $this->request['backid'] : '';
        $rollbackname = isset($this->request['backname']) ? $this->request['backname'] : '';
        $nextapprover = $this->request["nextapprover"];
        $nextsteptype = $this->request["nextsteptype"];
        $mode = !empty($this->request['mode']) ? $this->request['mode'] : 1;
        $mobi = new MobiApi();
        $json = $mobi->handle_case($caseid, $state, $remark, $enterpriseno, $userno, $pass, $sign, $attachmentid, $rollbackid, $rollbackname, $nextapprover, $nextsteptype, $mode);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 申请审批  审批催办
     */
    private function reminder_process(){
        $this->check_user();
        $caseid = $this->request["caseid"];
        $userno = $this->request["userno"];
        $enterpriseno = $this->request['enterpriseno'];
        $mobi = new MobiApi();
        $json = $mobi->remind_process($userno, $enterpriseno, $caseid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 申请审批  获取审批回退步骤
     */
    private function get_rollback_step(){
        $this->check_user();
        $caseid = $this->request["caseid"];
        $userno = $this->request["userno"];
        $enterpriseno = $this->request['enterpriseno'];
        $mobi = new MobiApi();
        $json = $mobi->get_caserollback_detail($caseid, $userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 申请审批  获取审批详情内的表格控件详情
     */
    private function get_table_info(){
        $this->check_user();
        $caseid = $this->request["caseid"];
        $enterpriseno = $this->request['enterpriseno'];
        $dynaformitemid = $this->request['dynaformitemid'];
        $mobi = new MobiApi();
        $json = $mobi->get_formtable_detail($caseid, $enterpriseno,$dynaformitemid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 申请审批 检测表单是否可用 dynaformid
     */
    private function check_form_status(){
        $this->check_user();
        $enterpriseno = $this->request['enterpriseno'];
        $dynaformid = $this->request['dynaformid'];
        $mobi = new MobiApi();
        $json = $mobi->check_dynaform_status($dynaformid, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     *活动  获取我的活动列表
     */
    private function my_activity_list() {
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $type = isset($this->request["type"]) ? $this->request["type"] : 0;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;

        $mobi = new MobiApi();
        $json = $mobi->get_myactivity_list($userno, $enterpriseno, $pageindex, $pagesize, $type, $lasttime);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 活动 获取活动详情
     */
    private function activity_info(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $id = isset($this->request["activityid"]) ? $this->request["activityid"] : 0;

        $mobi = new MobiApi();
        $json = $mobi->get_activity_detail($id, $userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 活动 加入活动
     */
    private function join_activity(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $id = isset($this->request["activeid"]) ? $this->request["activeid"] : 0;
        $status = isset($this->request["states"]) ? $this->request["states"] : 0;
        $reason = isset($this->request["reason"]) ? $this->request["reason"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->join_activity($userno, $enterpriseno, $id, $status, $reason);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 活动 添加活动
     */
    private function add_activity(){
        if (isset($this->request['method']) && $this->request['method'] == 'post') {
            $this->request = $_POST;
        }
        $this->check_user();
        $userno = isset($this->request['userno']) ? $this->request['userno'] : 0;
        $enterpriseno = isset($this->request['enterpriseno']) ? $this->request['enterpriseno'] : 0;
        $viewusers = empty($this->request['viewusers']) ? '' : $this->request['viewusers'];
        $tousers = empty($this->request['tousers']) ? '' : $this->request['tousers'];
        $type = isset($this->request['type']) ? $this->request['type'] : '';
        $title = isset($this->request['title']) ? trim($this->request['title']) : '';
        $starttime = isset($this->request['starttime']) ? $this->request['starttime'] : ''; 
        $endtime = isset($this->request['endtime']) ? $this->request['endtime'] : ''; 
        $address = isset($this->request['address']) ? $this->request['address'] : '';
        $introduction = isset($this->request['introduction']) ? $this->request['introduction'] : '';
        $attachments = isset($this->request['attachments']) ? $this->request['attachments'] : '';
        $contactphone = isset($this->request['contactphone']) ? $this->request['contactphone'] : '';
        $maxcount = isset($this->request['maxcount']) ? $this->request['maxcount'] : '';
        $price = isset($this->request['price']) ? $this->request['price'] : '';
        $cost = isset($this->request['cost']) ? $this->request['cost'] : '';
        $endjoin = isset($this->request['endjoin']) ? $this->request['endjoin'] : '';
        $description = isset($this->request['description']) ? $this->request['description'] : '';
        $activityid = isset($this->request['activityid']) ? $this->request['activityid'] : '';
        if(!empty($viewusers)){
            if(empty($tousers)){
                $viewusers = '';
            }else{
                $viewusers = $tousers;
            }
        }
        $mobi = new MobiApi();
        $json = $mobi->edit_activity($activityid, $userno, $enterpriseno, $viewusers, $tousers, $type, $title, $starttime, $endtime, $address, $introduction, $attachments, $contactphone, $maxcount, $price, $cost, $endjoin, $description);
   
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 活动 删除活动
     */
    private function del_activity(){
        $this->check_user();
        $userno = $this->request['userno'];
        $enterpriseno = $this->request['enterpriseno'];
        $activityid = $this->request['activityid'];
        $mobi = new MobiApi();
        $json = $mobi->del_activity($userno, $enterpriseno, $activityid);
   
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜 获取文件柜快捷方式列表
     */
    private function get_shortcut_list(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $mobi = new MobiApi();
        $json = $mobi->get_shortcut_list($userno, $enterpriseno);
        
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜 获取文件列表
     */
    private function get_dirfile(){
        $this->check_user();
        $userno = $this->request['userno'];
        $enterpriseno = $this->request['enterpriseno'];
        $dir = $this->request['dir'];
        $mobi = new MobiApi();
        $json = $mobi->get_dirfile($userno, $enterpriseno, $dir);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜 创建文件夹
     */
    private function create_dir(){
       $this->check_user();
       $userno = $this->request["userno"];
       $enterpriseno = $this->request["enterpriseno"];
       $level = $this->request['level'];
       $pid = $this->request['pid'];
       $directoryname = $this->request['name'];
       $type = $this->request['type'];
       $mobi = new MobiApi();
       $json = $mobi->create_directory($userno, $enterpriseno, $level, $pid, $directoryname, $type);
       $result = json_encode($json);
       $this->write_json($result);
    }
    /*
     * 文件柜 重命名文件或文件夹
     */
    private function rename_file(){
        $this->check_user();
        $userno = isset($this->request['userno']) ? $this->request['userno'] : '';
        $enterpriseno = isset($this->request['enterpriseno']) ? $this->request['enterpriseno'] : '';
        $id = isset($this->request['id']) ? $this->request['id'] : '';
        $newname = isset($this->request['name']) ? $this->request['name'] : '';
        $mobi = new MobiApi();
        $json = $mobi->rename_doc($userno, $enterpriseno, $id, $newname);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜 删除文件夹或者文件 如果是删除文件夹，需要同时删除文件夹底下的文件
     */
    private function del_file(){
        $this->check_user();
        $userno = $this->request['userno'];
        $enterpriseno = $this->request['enterpriseno'];
        $id = $this->request['fileid'];
        $mobi = new MobiApi();
        $json = $mobi->delete_doc($id, $userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜  共享文件
     */
    private function share_file(){
        $this->check_user();
        $enterpriseno = $this->request['enterpriseno'];
        $id = $this->request['fileid'];
        $isshare = $this->request['isshare'];
        $share = $this->request['share'];
        $mobi = new MobiApi();
        $json = $mobi->share_doc($id, $isshare, $share, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜   取消共享
     */
    private function cancle_share_file(){
        $this->check_user();
        $userno = $this->request['userno'];
        $did = $this->request['id'];
        $mobi = new MobiApi();
        $json = $mobi->cancle_share_doc($did, $userno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜  获取共享文件的共享人员
     */
    private function get_sharefile_user(){
        $this->check_user();
        $id = $this->request['fileid'];
        $enterpriseno = $this->request['enterpriseno'];
        $mobi = new MobiApi();
        $json = $mobi->get_doc_shareuser($id, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜  上传文件到文件柜
     */
    private function upload_file(){
        $this->check_user();
        $id = $this->request['id'];
        $pid = $this->request['pid'];
        $level = $this->request['level'];
        $oldname = $this->request['oldfilename'];
        $enterpriseno = $this->request['enterpriseno'];
        $userno = $this->request['userno'];
        $path = $this->request['path'];
        $size = $this->request['size'];
        $extension = $this->request['extension'];
        $filetype = intval($this->request['filetype']);
        $mobi = new MobiApi();
        $json = $mobi->add_doc($userno, $enterpriseno, $id, $pid, $level, $oldname, $path, $size, $extension, $filetype);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜  设置目标文件或文件夹属性
     */
    private function save_docpropertypanel_info(){
        $id = $this->request['id'];
        $enterpriseno = $this->request['enterpriseno'];
        $userno = $this->request['userno'];
        $property = $this->request['property'];
        $mobi = new MobiApi();
        $json = $mobi->save_docproperty($id, $userno, $enterpriseno, $property);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜 获取同事共享的文件
     */
    private function get_colleague_sharedoc(){
        $this->check_user();
        $userno = $this->request['userno'];
        $enterpriseno = $this->request['enterpriseno'];
        $mobi = new MobiApi();
        $json = $mobi->get_colleague_sharelist($userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜  手机断点上传 检查文件分块的总体上传情况
     */
    private function check_code(){
        $this->check_user();
        $hashcode = isset($this->request['hashcode']) ? $this->request['hashcode'] : '';
        $enterpriseno = isset($this->request['enterpriseno']) ? $this->request['enterpriseno'] : 0;
        $userno = isset($this->request['userno']) ? $this->request['userno'] : 0;
        Doo::loadClass('SdUpload');
        SdUpload::check_hashcode($hashcode,$enterpriseno,$userno);
    }
    /*
     * 文件柜  手机断点上传 添加文件记录
     */
    private function add_code(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $filename = isset($this->request['filename']) ? $this->request['filename'] : '';
        $size = isset($this->request['size']) ? $this->request['size'] : 0;
        $hashcode = isset($this->request['hashcode']) ? $this->request['hashcode'] : '';
        $blocks = isset($this->request['blocks']) ? $this->request['blocks'] : 0;
        $type = isset($this->request['type']) ? $this->request['type'] : 0; //后缀名，包括点号
        Doo::loadClass('SdUpload');
        SdUpload::add_hashcode($userno, $enterpriseno, $filename, $size, $hashcode, $blocks, $type);
    }
    /*
     * 文件柜  获取文件属性 
     */
    private function get_doc_property(){
        $this->check_user();
        $id = $this->request['id'];
        $enterpriseno = $this->request['enterpriseno'];
        $mobi = new MobiApi();
        $json = $mobi->get_doc_property($enterpriseno, $id);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜  修改或者删除快捷方式 
     */
    private function update_shortcut(){
        $this->check_user();
        $id = $this->request['shortcutid'];
        $enterpriseno = $this->request['enterpriseno'];
        $type = $this->request['type'];
        $value = $this->request['value'];
        
        $mobi = new MobiApi();
        $json = $mobi->edit_shortcut($enterpriseno, $id, $type, $value);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜   获取文件/文件夹的分享信息列表
     */
    private function get_share_list(){
        $this->check_user();
        $documentid = $this->request['documentid'];
        $enterpriseno = $this->request['enterpriseno'];
        Doo::loadClass('Doc');
        $res = Doc::get_docshare_list($documentid, $enterpriseno);
        if($res == -1){
            $json = array('returncode'=>  MobiApi::NO_RECODE_CODE);
        }else{
            $json = array('returncode'=>  MobiApi::SUCCESS_CODE,'contents'=>$res);
        }
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜    获取文件分享的条数,如果只有一条则返回该条的详情
     */
    private function get_share_content(){
        $this->check_user();
        $documentid = $this->request['documentid'];
        $enterpriseno = $this->request['enterpriseno'];
        Doo::loadClass('Doc');
        $res = Doc::get_share_count($documentid, $enterpriseno);
        if($res == -1){
            $json = array('returncode'=>  MobiApi::NO_RECODE_CODE);
        }else{
            $json = array('returncode'=>  MobiApi::SUCCESS_CODE,'content'=>$res);
        }
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜   设置共享
     */
    private function edit_doc_share(){
        $this->check_user();
        $userno = $this->request['userno'];
        $enterpriseno = $this->request['enterpriseno'];
        $documentid = intval($this->request['documentid']);
        $shareid = isset($this->request['shareid']) ? intval($this->request['shareid']) : 0;
        $users = isset($this->request['users']) ? $this->request['users'] : '';
        $department = isset($this->request['department']) ? $this->request['department'] : '';
        $position = isset($this->request['position']) ? $this->request['position'] : '';
        $role = isset($this->request['role']) ? $this->request['role'] : '';
        $sharetime = isset($this->request['sharetime']) ? $this->request['sharetime'] : '';
        $permission = isset($this->request['permission']) ? intval($this->request['permission']) : 4;
        $isnotice = isset($this->request['isnotice']) ? intval($this->request['isnotice']) : 0;
        Doo::loadClass('Doc');
        $res = Doc::edit_docshare_config($enterpriseno, $userno, $documentid, $shareid, $users, $department, $position, $role,$sharetime, $permission, $isnotice);
        if($res){
            $json = array('returncode'=>  MobiApi::SUCCESS_CODE);
        }else{
            $json = array('returncode'=>  MobiApi::ERROR_CODE);
        }
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 文件柜   取消共享
     */
    private function cancle_share_doc(){
        $this->check_user();
        $userno = $this->request['userno'];
        $enterpriseno = $this->request['enterpriseno'];
        $documentid = isset($this->request['documentid']) ? $this->request['documentid'] : 0;
        $shareid = isset($this->request['shareid']) ? $this->request['shareid'] : 0;
        Doo::loadClass('Doc');
        $res = Doc::cancle_document_share($enterpriseno, $userno, $documentid, $shareid);
        if($res){
            $json = array('returncode'=>  MobiApi::SUCCESS_CODE);
        }else{
            $json = array('returncode'=>  MobiApi::ERROR_CODE);
        }
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 客户  获取客户列表 
     * 其中type为0时获取我的客户  type为1时获取公司客户
     */
    private function get_customer_list(){
        $this->check_user();
        $userno = $this->request['userno'];
        $enterpriseno = $this->request['enterpriseno'];
        $updatetime = !empty($this->request['lastupdatetime']) ? $this->request['lastupdatetime'] : -1;
        $pageindex = isset($this->request['pageindex']) ? $this->request['pageindex'] : 1;
        $pagesize = isset($this->request['pagesize']) ? $this->request['pagesize'] : 10;
        $type = isset($this->request['type']) ? $this->request['type'] : 0;
        $mobi = new MobiApi();
        $json = $mobi->get_customer_list($userno, $enterpriseno, $updatetime, $pageindex, $pagesize, $type);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 客户  获取客户的详细信息
     */
    private function get_customer_info(){
        $this->check_user();
        $userno = $this->request['userno'];
        $id = $this->request['archivesid'];
        $mobi = new MobiApi();
        $json = $mobi->get_customer_detail($userno, $id);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 客户  获取客户的类型
     */
    private function get_customer_type(){
        $this->check_user();
        $userno = $this->request['userno'];
        $enterpriseno = $this->request['enterpriseno'];
        Doo::loadClass("Params");
        $list = Params::findParams($enterpriseno, $userno, 8);
        $arr = array();
        foreach ($list as $v) {
            $arr[] = array(
                'paramname' => $v['paramname'],
                'paramid' => $v['paramid']
                );
        }
        $json = array('returncode'=>MobiApi::SUCCESS_CODE,'contents'=>$arr);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 客户  添加客户档案
     */
    private function add_customer(){
        if (isset($this->request['method']) && $this->request['method'] == 'post') {
            $this->request = $_POST;
        }
        $this->check_user();
        $type = $this->request["status"];
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];

        $shortname = $this->request["name"];
        $custype = $this->request["category"];
        $fax = $this->request["fax"];
        $phone = $this->request["phone"];
        $manager = $this->request['manager'];
        $license = isset($this->request["attachmentid"]) ? $this->request["attachmentid"] : '';;
        $provinceid = isset($this->request['proviceid']) ? $this->request['proviceid'] : '';
        $provincename = isset($this->request['provice']) ? $this->request['provice'] : '';
        $cityid = isset($this->request['cityid']) ? $this->request['cityid'] : '';
        $cityname = isset($this->request['city']) ? $this->request['city'] : '';
        $districtid = isset($this->request['districtid']) ? $this->request['districtid'] : '';
        $districtname = isset($this->request['district']) ? $this->request['district'] : '';
        $streetname = isset($this->request['street']) ? $this->request['street'] : '';
        $contacts = isset($this->request['contacts']) ? $this->request['contacts'] : '';
        
        $mobi = new MobiApi();
        $json = $mobi->edit_customer($userno, $enterpriseno, $type, $shortname, $custype, $fax, $phone, $manager, $license, $provinceid, $provincename, $cityid, $cityname, $districtid, $districtname, $streetname, $contacts);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 客户  删除客户
     */
    private function del_customer(){
        $this->check_user();
        $userno = $this->request['userno'];
        $enterpriseno = $this->request['enterpriseno'];
        $id = $this->request['archivesid'];
        Doo::loadClass('Customer');
        $delete = new Customer();
        $delete->delete_customer($id, $userno, $enterpriseno);
        $json = array('returncode'=>MobiApi::SUCCESS_CODE);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 服务拜访  获取我的拜访列表
     */
    private function get_myvisit_list(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = empty($this->request['lasttime']) ? -1 : $this->request['lasttime'];
        $mobi = new MobiApi();
        $json = $mobi->get_myvisit_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 服务拜访  
     */
    private function get_myremark_list() {
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = empty($this->request['lasttime']) ? -1 : $this->request['lasttime'];
        $mobi = new MobiApi();
        $json = $mobi->get_myremark_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 服务拜访
     */
    private function get_otherrecorder_list() {
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = empty($this->request['lasttime']) ? -1 : $this->request['lasttime'];
        $mobi = new MobiApi();
        $json = $mobi->get_otherrecorder_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 新建服务或拜访
     * @param $isvisit 是否为拜访
     * @param $waitername 服务/拜访人员 默认为发送人
     */
    private function add_visit_service(){
        if (isset($this->request['method']) && $this->request['method'] == 'post') {
            $this->request = $_POST;
        }
        $this->check_user();
        $isvisit = $this->request['isvisit'];
        $starttime = $this->request['starttime'];
        $endtime = $this->request['endtime'];
        $visittype = $this->request['visittype']; //拜访方式
        $receptionname = $this->request['receptionname']; //接待人员
        $customerid = isset($this->request["customerid"]) ? $this->request["customerid"] : ''; //客户公司id
        $customername = $this->request["customername"]; //客户公司名字
        $enterpriseno = $this->request['enterpriseno'];
        $remarker = $this->request['remarker'];  //批阅人
        $waitername = $this->request['waitername']; //拜访人员 或拜访人员 默认为当前登录人
        $description = $this->request['description'];
        $attachmentid = $this->request['attachmentid'];
        $userno = $this->request['userno'];
        $address = $this->request['location_address'];
        $time = $this->request['location_time'];
        $point = $this->request['location_point'];
        $type = isset($this->request['type']) ? $this->request['type'] : -1;
        $request = $this->request;
        $mobi = new MobiApi();
        $json = $mobi->edit_visit($isvisit, $starttime, $endtime, $visittype, $receptionname, $customerid, $customername, $enterpriseno, $remarker, $waitername, $description, $attachmentid, $userno, $address, $time, $point,$type,$request);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 服务拜访 获取详情
     */
    private function get_visit_info(){
        $this->check_user();
        $visitid = isset($this->request['visitid']) ? intval($this->request['visitid']) : 0;
        $enterpriseno = $this->request['enterpriseno'];

        $mobi = new MobiApi();
        $json = $mobi->get_visit_detail($enterpriseno, $visitid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 服务拜访  获取 xxxx类型
     */
    private function get_visit_types(){
        $this->check_user();
        Doo::loadClass('Vist');
        $enterpriseno = $this->request['enterpriseno'];
        $opt = array(
            'where'=>'enterpriseno=? and status=?',
            'param'=>array($enterpriseno,1101),
            'asArray'=>true
        );
        $list = Vist::getVisitTypes($opt);

        $arr = array();
        foreach ($list as $v) {
            $arr[] = array(
                'vistname' => $v['name'],
                'visttypeid' => $v['visttypeid']
                );
        }
        $result = json_encode(array('returncode' => MobiApi::SUCCESS_CODE, 'contents' => $arr));
        $this->write_json($result);
    }
    /*
     * 服务拜访
     */
    private function set_isread(){
        Doo::loadClass('Vist');
        $enterpriseno = $this->request['enterpriseno'];
        $userno = $this->request['userno'];
        $visitid = $this->request['visitid'];

        $mobi = new MobiApi();
        $json = $mobi->set_visit_read($userno, $enterpriseno, $visitid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 服务拜访   删除服务拜访
     */
    private function delete_visit(){
        Doo::loadClass('Vist');
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $visitid = isset($this->request['visitid']) ? $this->request['visitid'] : 0;
        $mobi = new MobiApi();
        $json = $mobi->del_visit($userno,$enterpriseno,$visitid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 自定义表单   获取自定义表单的元素
     */
    private function get_customform_item(){
        $this->check_user();
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $type = isset($this->request['type']) ? $this->request['type'] : 4;
        Doo::loadClass('CustomForm');
        $itemlist = CustomForm::get_custom_dynaformitem($enterpriseno, $type);
        $json = array('returncode'=>  MobiApi::SUCCESS_CODE,'contents'=>$itemlist);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 内部邮件  发送内部邮件
     */
    private function add_pms(){
        if (!isset($this->request['method']) || $this->request['method'] == 'post') {
            //默认为post方式，发送数据过来
            //改进提交内容太多
            $this->request = $_POST;
        }
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $title = isset($this->request["title"]) ? $this->request["title"] : 0;
        $content = isset($this->request["content"]) ? $this->request["content"] : 0;
        $readers = isset($this->request["readers"]) ? $this->request["readers"] : "";
        $attachmentid = isset($this->request["attachmentid"]) ? $this->request["attachmentid"] : 0;
        $cctousers = isset($this->request['cctousers']) ? $this->request['cctousers'] : '';
        $mobi = new MobiApi();
        $json = $mobi->send_pms($userno, $enterpriseno, $title, $content, $readers, $attachmentid, $cctousers);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 内部邮件  收件箱
     */
    private function receive_pms_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        $mobi = new MobiApi();
        $json = $mobi->get_inbox_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 内部邮件   发件箱列表
     */
    private function send_pms_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        $mobi = new MobiApi();
        $json = $mobi->get_outbox_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 内部邮件  邮件详情
     */
    private function get_pms_info(){
        $this->check_user();
        $pmsid = isset($this->request["pmsid"]) ? $this->request["pmsid"] : 0;
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->get_pms_detail($pmsid, $userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 内部邮件  投票
     */
    private function post_vote(){
        $this->check_user();
        $voteoptions = isset($this->request["voteoptions"]) ? $this->request["voteoptions"] : 0;
        $voteid = isset($this->request["voteid"]) ? $this->request["voteid"] : 0;
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pmsid = $this->request['pmsid'];
        Doo::loadClass('Vote');
        Doo::loadClass('Pms');
        $res = Vote::post_uservote($userno, $enterpriseno, $voteid, $voteoptions);
        Pms::update_pmsvote_votedusers($pmsid, $voteid, $userno);
        if($res['success']){
            $json = array('returncode'=> MobiApi::SUCCESS_CODE);
        }else{
            $json = array('returncode'=> MobiApi::ERROR_CODE,'message'=>$res['message']);
        }
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 内部邮件  回复邮件
     */
    private function post_comment(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $id = isset($this->request["id"]) ? $this->request["id"] : 0;
        $content = isset($this->request["content"]) ? $this->request["content"] : 0;
        $attachments = isset($this->request["attachments"]) ? $this->request["attachments"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->reply_email($userno, $id, $content, $attachments);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 内部邮件  获取邮件的评论列表
     */
    private function get_replay_list(){
        $this->check_user();
        $id = isset($this->request["id"]) ? $this->request["id"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->get_pmsreply_list($id);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 内部邮件  删除内部邮件
     */
    private function del_pms(){
        $userno = $this->request['userno'];
        $enterpriseno = $this->request['enterpriseno'];
        $id = $this->request['id'];
        $mobi = new MobiApi();
        $json = $mobi->delete_pms($id, $userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 评论相关  获取评论列表
     */
    private function get_comment_list(){
        $this->check_user();
        $userno = $this->request['userno'];
        $id = isset($this->request["id"]) ? $this->request["id"] : 0;
        $type = isset($this->request["type"]) ? ucfirst(($this->request["type"])) : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $canview = isset($this->request['canview']) ? $this->request['canview'] : true;
        $mobi = new MobiApi();
        $json = $mobi->get_comment_list($userno,$enterpriseno, $id, $type, $pageindex, $pagesize,$canview);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 评论相关   添加评论
     */
    private function add_comment(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $id = isset($this->request["id"]) ? $this->request["id"] : 0;
        $content = isset($this->request["content"]) ? $this->request["content"] : 0;
        $type = isset($this->request["type"]) ? ucfirst(($this->request["type"])) : 0;
        $mobi = new MobiApi();
        $json = $mobi->add_comment($userno, $enterpriseno, $id, $type, $content);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 手机注册  获取申请加入信息
     */
    private function get_apply_message(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $mobi = new MobiApi();
        $json = $mobi->get_reg_message($userno, $enterpriseno, $pageindex, $pagesize);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 手机注册  同意加入公司
     */
    private function agree_join(){
        $operateuserno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $userno = $this->request["applyuserno"]; 
        $id = $this->request['id'];
        $mobi = new MobiApi();
        $json = $mobi->agree_join_enterprise($operateuserno, $enterpriseno, $userno, $id);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 手机注册  拒绝申请加入公司
     */
    private function refuse_join(){
        $operateuserno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $userno = $this->request["applyuserno"];
        $id = $this->request['id'];
        $mobi = new MobiApi();
        $json = $mobi->refuse_join_enterprise($operateuserno, $enterpriseno, $userno, $id);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     *手机注册 帮助注册(只允许手机号码)
     */
    private function help_join(){
        $this->check_user();
        $name = $this->request['name'];
        $salt = $this->request['salt'];
        $department = isset($this->request['department']) ? $this->request['department'] : 0;
        $position = isset($this->request['position']) ? $this->request['position'] : 0;
        $role = isset($this->request['role']) ? $this->request['role'] : "";
        $status = isset($this->request['status']) ? $this->request['status'] : Enum::getStatusType("Normal");
        $mobile = isset($this->request['phone']) ? $this->request['phone'] : "";
        $enterpriseno = $this->request['enterpriseno'];
        $user_email = $this->request['email'];
        $mobi = new MobiApi();
        $json = $mobi->help_join_enterprise($name, $salt, $department, $position, $role, $status, $mobile, $enterpriseno, $user_email);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 网络传真  获取传真收件箱列表
     */
    private function get_fax_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        $mobi = new MobiApi();
        $json = $mobi->get_faxinbox_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 网络传真 获取网络传真发件箱列表
     */
    private function fax_sent_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        $mobi = new MobiApi();
        $json = $mobi->get_faxoutbox_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 网络传真  发送传真
     */
    private function add_fax(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $destnumber = isset($this->request['destnumber']) ? $this->request['destnumber'] : '';
        $title = isset($this->request['title']) ? $this->request['title'] : '';
        $files = isset($this->request['files']) ? $this->request['files'] : '';
        $sendtime = isset($this->request['sendtime']) ? $this->request['sendtime'] : '';
        $content = isset($this->request['content']) ? $this->request['content'] : '';
        $mobi = new MobiApi();
        $json = $mobi->send_fax($userno, $enterpriseno, $destnumber, $title, $files, $sendtime, $content);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 网络传真 获取传真详情
     */
    private function get_fax_info(){
        $this->check_user();
        $type = isset($this->request["type"]) ? $this->request["type"] : 0;
        $faxid = isset($this->request["faxid"]) ? $this->request["faxid"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->get_fax_detail($faxid, $type);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 任务  获取任务列表
     */
    private function get_my_task_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        $mobi = new MobiApi();
        $json = $mobi->get_mytask_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 任务  获取我验收的任务列表
     */
    private function get_accept_task_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        $mobi = new MobiApi();
        $json = $mobi->get_accepttask_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 任务  获取共享给我的任务
     */
    private function get_all_task(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        $mobi = new MobiApi();
        $json = $mobi->get_all_task($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 任务  添加 编辑任务
     */
    private function create_task(){
        if (isset($this->request['method']) && $this->request['method'] == 'post') {
            $this->request = $_POST;
        }
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $startdate = $this->request["sdate"];
        $finishdate = $this->request["edate"];
        $notes = $this->request["notes"];
        $priority = $this->request["priority"];
        $cousernos = $this->request["joinusers"];
        $accepteruserid = $this->request["accepterusers"];
        $taskname = isset($this->request["taskname"]) ? $this->request["taskname"] : 0;
        $attachmentids = isset($this->request["attachments"]) ? $this->request["attachments"] : "";
        $joinusers = isset($this->request["handeler"]) ? $this->request['handeler'] : '';
        $parentid = isset($this->request["parentid"]) ? $this->request["parentid"] : 0;
        $viewpermission = isset($this->request['viewpermission']) ? $this->request['viewpermission'] : 2;
        $modelid = isset($this->request['modelid']) ? $this->request['modelid'] : '';
        if($viewpermission == 3){
            $viewusers = isset($this->request['viewuserno']) ? $this->request['viewuserno'] : '';
        }else{
            $viewusers = '';
        }
        $taskid = isset($this->request["taskid"]) ? $this->request["taskid"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->edit_task($userno, $enterpriseno, $startdate, $finishdate, $notes, $priority, $joinusers, $accepteruserid, $taskname, $attachmentids, $cousernos, $parentid, $taskid,$viewpermission,$viewusers,$modelid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 任务  开始执行任务
     */
    private function start_task(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $taskid = isset($this->request["taskid"]) ? $this->request["taskid"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->run_task($userno, $taskid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 任务  设置任务进度
     */
    private function set_task_percentage(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $taskid = isset($this->request["taskid"]) ? $this->request["taskid"] : 0;
        $progress = $this->request["progress"];
        $mobi = new MobiApi();
        $json = $mobi->save_task_progress($userno, $taskid, $progress);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 任务  验收任务
     */
    private function accept_task(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $taskid = isset($this->request["taskid"]) ? $this->request["taskid"] : 0;
        $content = isset($this->request["content"]) ? $this->request["content"] : 0;
        $status = isset($this->request["status"]) ? $this->request["status"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->accept_task($userno, $taskid, $content, $status);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 任务  获取任务详情
     */
    private function get_task_info(){
        $this->check_user();
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $taskid = isset($this->request["taskid"]) ? $this->request["taskid"] : 10;
        $modelid = isset($this->request["modelid"]) ? $this->request["modelid"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->get_task_detail($enterpriseno,$taskid,$modelid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 任务  申请验收任务
     */
    private function request_accepttask(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $taskid = isset($this->request["taskid"]) ? $this->request["taskid"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->apply_accept($taskid,$userno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 任务  删除任务
     */
    private function delete_task(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $taskid = isset($this->request["taskid"]) ? $this->request["taskid"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->del_task($taskid,$userno,$enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 系统消息  获取系统消息列表
     */
    private function get_msg_list(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $lasttime = !empty($this->request["nowtime"]) ? $this->request["nowtime"] : -1;
        $mobi = new MobiApi();
        $json = $mobi->get_sysmsg_list($userno, $enterpriseno, $pageindex, $pagesize, $lasttime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 系统消息  获取最后一条推送消息
     */
    private function get_last_notification(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->get_final_notification($userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 日程   获取日程列表
     */
    private function get_schedule_list(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $starttime = $this->request["starttime"];
        $endtime = $this->request["endtime"];
        $mobi = new MobiApi();
        $json = $mobi->get_schedule_list($userno, $enterpriseno, $starttime, $endtime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 日程  获取日程详细信息
     */
    private function get_schedule_info(){
        $this->check_user();
        $scheduleid = $this->request["scheduleid"];
        $mobi = new MobiApi();
        $json = $mobi->get_schedule_detail($scheduleid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 日程  添加日程
     */
    private function add_schedule(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $title = $this->request["title"];
        $startdate = $this->request["starttime"];
        $finishdate = $this->request["endtime"];
        $address = $this->request["address"];
        $readuser = $this->request["readuser"];
        $attachment = $this->request["attachmentid"];
        $mobi = new MobiApi();
        $json = $mobi->edit_schedule($userno, $enterpriseno, $title, $startdate, $finishdate, $address, $readuser, $attachment);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 日程 //获取我关注的人的日程数量
     */
    private function get_attention(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $starttime = $this->request["starttime"];
        $endtime = $this->request["endtime"];
        $mobi = new MobiApi();
        $json = $mobi->get_attention_list($userno, $enterpriseno, $starttime, $endtime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 日程 编辑关注人员
     */
    private function add_attention(){
        $this->check_user();
        $users = $this->request['userid'];
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $mobi = new MobiApi();
        $json = $mobi->edit_attention($users, $userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 日程  获取我可以建日程的人员id
     */
    private function get_arrange_permission(){
        $this->check_user();
        $enterpriseno = $this->request['enterpriseno'];
        $userno = $this->request['userno'];
        $mobi = new MobiApi();
        $json = $mobi->get_arrange_permission($userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 排班  获取排班列表
     */
    private function get_arrangement_list(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $starttime = $this->request["starttime"];
        $endtime = $this->request["endtime"];
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $mobi = new MobiApi();
        $json = $mobi->get_arrangement_list($userno, $enterpriseno, $starttime, $endtime, $pageindex, $pagesize);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 排班 获取排班地区
     */
    private function get_arrangementlist_byunit(){
        $this->check_user();
        $enterpriseno = $this->request["enterpriseno"];
        $mobi = new MobiApi();
        $json = $mobi->get_area_arrangementlist($enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 排班 获得某地区排班
     */
    private function get_alllist_byunit(){
        $this->check_user();
        $enterpriseno = $this->request["enterpriseno"];
        $starttime = $this->request["starttime"];
        $endtime = $this->request["endtime"];
        $configid = $this->request["unitid"];
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $mobi = new MobiApi();
        $json = $mobi->get_area_alllist($enterpriseno, $starttime, $endtime, $configid, $pageindex, $pagesize);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 排班  获取节假日设置列表
     */
    private function get_holidays_list(){
        $this->check_user();
        $enterpriseno = $this->request["enterpriseno"];
        $startdate = $this->request["starttime"];
        $enddate = $this->request["endtime"];
        $mobi = new MobiApi();
        $json = $mobi->get_holidays_list($enterpriseno, $startdate, $enddate);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 值班  值班签到
     */
    private function sign_duty(){
        $this->check_user();
        $id = $this->request['arrangeid'];
        $type = $this->request['type'];
        $mobi = new MobiApi();
        $json = $mobi->signin_duty($id, $type);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 获取某个同事的信息，返回的信息不包含id
     */
    private function get_user_info(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $mobi = new MobiApi();
        $json = $mobi->get_user_detail($userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 附件相关  根据附件ID获取附件信息
     */
    private function get_attachments(){
        $this->check_user();
        $files = isset($this->request["files"]) ? $this->request["files"] : '';
        $mobi = new MobiApi();
        $json = $mobi->get_attachment_list($files);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 获取组件消息条数
     */
    private function get_last_setinfo(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->get_app_msgcount($userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 修改用户给人资料
     */
    private function update_user(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $signature = isset($this->request["signature"]) ? $this->request["signature"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->edit_userinfo($userno, $signature);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 获取系统日志列表
     */
    private function get_syslog_list(){
        $enterpriseno = $this->request["enterpriseno"];
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 10;
        $endtime = !empty($this->request['endtime']) ? $this->request['endtime'] : date('Y-m-d H:i:s');
        $actor = $this->request['actor'];
        $starttime = $this->request['starttime'];
        $keyword = $this->request['keyword'];
        $type = $this->request['type'];
        $mobi = new MobiApi();
        $json = $mobi->get_systemlog_list($enterpriseno, $pageindex, $pagesize, $actor, $starttime, $endtime, $keyword, $type);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 获取用户的权限信息
     */
    private function get_permission(){
        $this->check_user();
        $userno = $this->request["userno"];
        $enterpriseno = $this->request["enterpriseno"];
        $mobi = new MobiApi();
        $json = $mobi->get_permission_list($userno, $enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 同事录  获取同事id
     */
    private function get_colleague(){
        $this->check_user();
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $lastupdatetime = isset($this->request['lastupdatetime']) ? $this->request['lastupdatetime'] : 0;
        $pagesize = isset($this->request['pagesize']) ? $this->request['pagesize'] : 10000;
        $pageindex = isset($this->request['pageindex']) ? $this->request['pageindex'] : 1;
        $flag = isset($this->request['flag']) ? $this->request['flag'] : FALSE;
        $hasname = isset($this->request['hasname']) ? $this->request['hasname'] : '';  //hasname为iphone端加载同事id时获取名字
        
        $mobi = new MobiApi();
        if(!$flag){
            $json = $mobi->get_employee_userno($enterpriseno, $lastupdatetime, $pageindex, $pagesize); //旧版本同事加载
        }else{
            $json = $mobi->get_eudp_desc($enterpriseno, $lastupdatetime, $pageindex, $pagesize,$hasname);
        }
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 同事录  获取同事列表
     */
    private function get_colleague_list(){
        if (isset($this->request['method']) && $this->request['method'] == 'post') {
            //默认为post方式，发送数据过来
            //改进提交内容太多
            $this->request = $_POST;
        }
        $this->check_user();
        $colleaguenos = isset($this->request["colleaguenos"]) ? $this->request["colleaguenos"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $pageindex = isset($this->request["pageindex"]) ? $this->request["pageindex"] : 1;
        $pagesize = isset($this->request["pagesize"]) ? $this->request["pagesize"] : 20;
        $departmentid = isset($this->request["departmentid"]) ? $this->request["departmentid"] : -1;
        $positionid = isset($this->request["positionid"]) ? $this->request["positionid"] : -1;
        $mobi = new MobiApi();
        $json = $mobi->get_colleague_list($enterpriseno, $colleaguenos, $pageindex, $pagesize, $departmentid, $positionid);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 同事录  获取组织架构
     */
    private function get_organization_list(){
        $this->check_user();
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $updatetime = $this->request['lastupdatetime'];
        $mobi = new MobiApi();
        $json = $mobi->get_orgs_list($enterpriseno, $updatetime);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 同事录   获取职位列表
     */
    private function get_position_list(){
        $this->check_user();
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->get_position_list($enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 公共删除方法
     */
    private function comm_del(){
        $this->check_user();
        $type = $this->request['type'];
        $id = $this->request['id'];
        $mobi = new MobiApi();
        $json = $mobi->del($id, $type);
        $result = json_encode($json);
        $this->write_json($result);
    }
    /*
     * 获取人员的部门职位信息 可能是多部门 多职位
     */
    private function get_employee_detail(){
        $this->check_user();
        $userno = isset($this->request["userno"]) ? $this->request["userno"] : 0;
        $enterpriseno = isset($this->request["enterpriseno"]) ? $this->request["enterpriseno"] : 0;
        $mobi = new MobiApi();
        $json = $mobi->get_employee_postdetail($userno,$enterpriseno);
        $result = json_encode($json);
        $this->write_json($result);
    }
}

?>