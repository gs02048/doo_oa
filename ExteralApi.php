<?php
/**
 * Description of OpenApi
 *        翼办公对信任外部应用开放接口
 * @author Administrator
 */
class ExteralApi {
    /*
     * 用户数据同步
     * @param $ext_userid 外部应用系统用户id
     * @param $ext_username 外部应用系统用户名
     * @param $ext_mobile  用户手机号
     * @param $ext_email   用户邮箱
     * 检查ext_userid是否已存在external_api_users表中如果有则更新external_api_users表信息,无则找出改账号在oa.cn里的对应账号信息并插入
     */
    public static function sync_user($ext_userid,$ext_username,$ext_mobile,$ext_email){
        Doo::loadModel('ExternalApiUsers');
        $e = new ExternalApiUsers();
        $e->mapuserno = $ext_userid;
        $u = $e->getOne();
        if($u == null){       //无该外部应用账号
            Doo::loadModel('VwEnterpriseEmployeeUser');
            Doo::loadClass('Enum');
            $delete = Enum::getStatusType('Delete');
            $m = new VwEnterpriseEmployeeUser();
            $opt = array(
                'select'=>'enterpriseno,userno',
                'where'=>'mobile=? AND status<>?',
                'param'=>array($ext_mobile,$delete),
                'asArray'=>true
            );
            $content = $m->getOne($opt);
            if($content != NULL){   //在翼办公中找到对应账号
                $normal = Enum::getStatusType('Normal');
                $userno = $content['userno'];
                $enterpriseno = $content['enterpriseno'];
                $e = new ExternalApiUsers();
                $e->userno = $userno;
                $e->enterpriseno = $enterpriseno;
                $e->mapuserno = $ext_userid;
                $e->mapusername = $ext_username;
                $e->mapusermobile = $ext_mobile;
                $e->mapuseremail = $ext_email;
                $e->createtime = $e->updatetime = date(DATE_ISO8601);
                $e->status = $normal;
                $e->insert();
                return true;
            }else{      //在翼办公中找不到对应账号信息
                return false;
            }
        }else{               //已有该账号信息
            $e->uid = $u->uid;
            $e->mapuseremail = $ext_email;
            $e->mapusermobile = $ext_mobile;
            $e->mapusername = $ext_username;
            $e->updatetime = date(DATE_ISO8601);
            $e->update();
            return true;
        }
    }
    /*
     * 根据外部应用系统的id来获取改用户再oa系统里面的信息
     */
    public static function get_user_byextid($ext_userid){
        Doo::loadModel('ExternalApiUsers');
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        $e = new ExternalApiUsers();
        $e->mapuserno = $ext_userid;
        $e->status = $normal;
        return $e->getOne();
    }
    /*
     * 返回数据时将oa系统的用户id转换为外部应用系统的用户id
     */
    public static function userno_to_extid($oauserno){
        $str = '';
        if(!empty($oauserno)){
            Doo::loadModel('ExternalApiUsers');
            Doo::loadClass('Enum');
            $normal = Enum::getStatusType('Normal');
            $opt = array('select'=>'GROUP_CONCAT(mapuserno) AS str','where'=>"userno IN ($oauserno) AND status=$normal",'asArray'=>true);
            $m = new ExternalApiUsers();
            $i = $m->getOne($opt);
            $str = $i['str'];
            if($str == NULL) $str = '';
        }
        return $str;
    }
    /*
     * 外部应用系统用户id转化为oa系统userno
     */
    public static function extid_to_userno($extuserid,$enterpriseno){
        $str = '';
        if(!empty($extuserid)){
            Doo::loadModel('ExternalApiUsers');
            Doo::loadClass('Enum');
            $normal = Enum::getStatusType('Normal');
            $opt = array('select'=>'GROUP_CONCAT(userno) AS str','where'=>"mapuserno IN ($extuserid) AND enterpriseno=$enterpriseno AND status=$normal",'asArray'=>true);
            $m = new ExternalApiUsers();
            $i = $m->getOne($opt);
            if($i == NULL) return '';
            $str = $i['str'];
        }
        return $str;
    }
    /*
     * 获取翼办公组织信息
     * @return array
     * fileds:1、id部门id 2、name部门名称 3、parentid部门的父级id 4、children部门的完整路径 5、displayorder部门排序 6、lastupdatetime最后更新时间
     */
    public static function get_org_list($enterpriseno){
        Doo::loadModel('EnterpriseDepartment');
        Doo::loadClass('Enum');
        $normal = Enum::getStatusType('Normal');
        if(empty($enterpriseno)) return -1;
        $d = new EnterpriseDepartment();
        $o = array(
            'select'=>'departmentid AS id,name,parentid,children,displayorder,updatetime AS lastupdatetime',
            'where'=>'enterpriseno=? AND status=?',
            'params'=>array($enterpriseno,$normal),
            'asArray'=>true
        );
        $list = $d->find($o);
        return $list;
    }
    /*
     * 内部邮件 发送内部邮件
     * @param $ext_userid 原应用用户id
     * @param $title      内部邮件标题
     * @param $tousers    内部邮件收件人
     * @param $cctousers  内部邮件抄送人
     * @param $content    内部邮件正文内容
     * @param $attachments 内部邮件附件
     * @return bool true or false
     */
    public static function send_pms($ext_userid,$title,$tousers,$cctousers,$content,$attachments){
        $info = self::get_user_byextid($ext_userid);
        if($info == NULL) return FALSE;
        $userno = $info->userno;
        $enterpriseno = $info->$enterpriseno;
        $tousers = self::extid_to_userno($tousers, $enterpriseno);
        $cctousers = self::extid_to_userno($cctousers, $enterpriseno);
        Doo::loadClass('Pms');
        Doo::loadClass('Exterprise');
        Doo::loadClass('Enum');
        $userinfo = Exterprise::getUserInfo(array('where' => "userno={$userno}"));
        $createtime = date('Y-m-d H:i:s');
        $viewusers = $userno;
        $lastreplytime = date('Y-m-d H:i:s');
        $lastreplyuser = $userno;
        $status = Enum::getStatusType('Normal');
        $id = Pms::add_pms(0, $userinfo->username, $userno, "", $tousers, $enterpriseno, $title, $content, $viewusers, $lastreplytime, $lastreplyuser, $cctousers, $type = 0, $status = 1101, $attachments, 0, '', 0, 0, $createtime = '');
        if($id < 0) return FALSE;
        Pms::addSysLog($id, 112, $userno, $userinfo->username, $enterpriseno);
        return TRUE;
    }
    /*
     * 文件上传
     * @param $userno 上传者的用户id
     * @param $enterpriseno 上传者所在公司
     * @param $name  上传的文件名
     * @param $size 上传文件大小
     * @param $tmp_name 上传文件在$_FILES预定义变量中的tmp_name 
     * @return  success id,name,size
     *          fail    false
     */
    public static function upload_file($userno,$enterpriseno,$name,$size,$tmp_name){
        Doo::loadClass('Guid');
        $guid = new guid();
        $pos = strrpos($name, "."); //取得文件名中后缀名的开始位置
        $ext =  strtolower(substr($name, $pos)); //取得后缀名，包括点号

        $y = date('Y');
        $m = date('m');
        $d = date('j');
        $tempfilepath = "userfiles/$userno/" . $y . "/" . $m . "/" . $d . "/";
        $fullpath = Doo::conf()->SITE_PATH . '/protected/' . $tempfilepath;
        if (!file_exists($fullpath)) {
            mkdir($fullpath, 0777, true);
        }
        $newfileName = $userno . "_" . str_replace("-", "", $guid->toString()) . $ext;
        $fullpath = $fullpath . $newfileName;
        if (move_uploaded_file($tmp_name, $fullpath)) {
            $filepath = $tempfilepath . $newfileName;

            if($ext=='.jpg' || $ext=='.jpeg' || $ext=='.png' || $ext=='.gif' || $ext=='.bmp') {
                $dstimage = Doo::conf()->SITE_PATH . '/protected/' . $tempfilepath. $userno . "_" . str_replace("-", "", $guid->toString()).'_thm0' . $ext;
                $tnm_width = 240;
                $tnm_height = 180;
                $this->imagezoom($fullpath, $dstimage, $tnm_width, $tnm_height);
            }

            //保存到数据库
            Doo::loadModel("EnterpriseAttachments");
            $m = new EnterpriseAttachments();
            $m->createtime = Date(DATE_ISO8601);
            $m->downloads = 0;
            $m->filename = $name;
            $m->filepath = 'protected/' . $filepath;
            $m->filetype = $ext;
            $m->filesize = $size;
            $m->userno = $userno;
            $m->enterpriseno = $enterpriseno;
            $m->status = 1101;
            $m->attachmentid = $m->insert();

            $r = array('id'=>$m->attachmentid,'name'=>$name,'size'=>$size);
        } else {
            $r = FALSE;
        }
        return $r;
    }
    /*
     * 下载文件
     * @param $attachmentid 附件id
     * 获取附件名字,路径，改变head下载
     */
    public static function download_file($attachmentid){
        if ($attachmentid == 0)
            return;
        Doo::loadModel("EnterpriseAttachments");
        $a = new EnterpriseAttachments();
        $m = $a->getOne(array('where' => 'attachmentid=' . $attachmentid));
        if ($m == null)
            return;
        $filename = $m->filename; 
        $filepath = $m->filepath; 
        Doo::loadClass("Download");
        $down = new Download();
        $down->is_attachment = true;
        $down->Download($filepath, $filename);
        exit;
    }
    /*
     * 内部邮件  收件箱
     * @return array
     * fileds 1、id内部邮件id 2、title标题 3、content内容 4、fromuser发件人 5、sendtime创建时间 6、attachments附件id,7、lastupdatetime最后更新时间
     */
    public static function get_inbox_list($userno,$enterpriseno,$pagesize,$pageindex){
        Doo::loadModel('EnterprisePms');
        Doo::loadClass('Enum');
        Doo::loadClass('Exterprise');
        Doo::loadClass('Attachment');
        //获取账号创建时间
        $limit_time = '';
        $option['where'] = 'userno = ? AND enterpriseno = ?';
        $option['param'] = array($userno, $enterpriseno);
        $useremployee_model = Exterprise::getUserInfo($option);
        if ($useremployee_model != null) {
            $limit_time = $useremployee_model->createtime;
        }
        $page = ($pageindex - 1) * $pagesize;
        $normal = Enum::getStatusType('Normal');
        $opt = array(
            'select'=>'pmsid AS id,subject AS title,message AS content,msgfromid AS fromuser,createtime AS sendtime,attachments,updatetime AS lastupdatetime',
            'where' => 'enterpriseno=? AND ((msgfromid = ?  AND replycount != ?) OR (FIND_IN_SET(?,msgtoid) AND msgfromid != ?) OR (msgfromid != ? AND msgtoid = ? AND createtime >= ?) OR (FIND_IN_SET(?,cctousers) AND msgfromid != ?)) AND status=? AND (NOT FIND_IN_SET(?,deleteusers))',
            'param' => array($enterpriseno, $userno, 0, $userno, $userno, $userno, 0, $limit_time, $userno, $userno,$normal,$userno),
            'desc' => 'lastreplytime',
            'limit'=>($page < 0 ? 0 : $page) . ',' . $pagesize,
            'asArray'=>true
        );
        $p = new EnterprisePms();
        $s = $p->count($opt);
        $l = $p->find($opt);
        foreach ($l as $k=>$i){
            $fromuser = self::userno_to_extid($i['fromuser']);
            $l[$k]['fromuser'] = $fromuser;
            $attres = self::get_att_info($i['attachments']);
            $l[$k]['attachments'] = $attres;
        }
        $now = time();
        $res = array('total'=>$s,'contents'=>$l,'nowtime'=>$now);
        return $res;
    }
    /*
     * 根据附件id串获取附件的名字信息
     * @return array
     * fileds 1、id附件id,2、name附件名字
     */
    private static function get_att_info($i){
        $k = '';
        if($i != ''){
            Doo::loadClass('Enum');
            $n = Enum::getStatusType('Normal');
            Doo::loadModel("EnterpriseAttachments");
            $m = new EnterpriseAttachments();
            $opt = array(
                'select'=>'attachmentid AS id,filename AS name',
                'where'=>"attachmentid in ($i) and status=$n",
                'asArray'=>true
            );
            $l = $m->find($opt);
            if($l == NULL) return $k;
            return $l;
        }
        return $k;
    }
    /*
     * 公告  获取公告列表
     * @return array
     * fileds  : 1、id公告id 2、title公告标题 3、content 公告内容 4、fromuser 发送人 5、publishdate 公告发布日期 6、attachments附件id 7、updatetime 公告更新时间
     */
    public static function get_news_list($userno,$enterpriseno,$pagesize,$pageindex){
        Doo::loadClass('Enum');
        Doo::loadClass('News');
        Doo::loadModel('EnterpriseNews');
        Doo::loadClass('Exterprise');
        $status = Enum::getStatusType('Normal');
        News::select_top_status($enterpriseno);
        $employee = Exterprise::getEmployeeInfo($userno, $enterpriseno);
        $e_createtime = $employee->createtime; //该员工加入公司的时间
        $page = (($pageindex - 1) * $pagesize);
        $opt = array(
            'select'=>'newsid AS id,title,content,userno AS fromuser,publishdate AS pubdate,attachmentids AS attachments,updatetime AS lastupdatetime',
            'where'=>'publishdate>=? AND (FIND_IN_SET(?,receiptusers) OR (receiptusers="")) AND enterpriseno=? AND status=?',
            'param'=>array($e_createtime, $userno, $enterpriseno,$status),
            'desc_1'=>'istop',
            'desc_2'=>'publishdate',
            'limit'=>($page < 0 ? 0 : $page) . ',' . $pagesize,
            'asArray'=>true
        );
        $n = new EnterpriseNews();
        $count = $n->count($opt);
        $list = $n->find($opt);
        foreach ($list as $key=>$value){
            $fromuser = self::userno_to_extid($value['fromuser']);
            $list[$key]['fromuser'] = $fromuser;
            $attres = self::get_att_info($value['attachments']);
            $list[$key]['attachments'] = $attres;
        }
        $now = time();
        $res = array('total'=>$count,'contents'=>$list,'nowtime'=>$now);
        return $res;
    }
    /*
     * 公告  发送公告
     * @param $userno       用户id
     * @param $enterpriseno 公司id
     * @param $title        公告标题
     * @param $content      公告详情
     * @param $tousers      公告受众
     * @param $type         公告类型
     * @param $attachments  公告附件id
     * 
     */
    public static function send_news($userno,$enterpriseno,$title,$content,$tousers,$type,$attachments){
        Doo::loadClass('News');
        Doo::loadClass('SysMessage');
        Doo::loadClass("Exterprise");
        Doo::loadClass('Params');
        Doo::loadClass("Enum");

        $fields = array();
        $fields['userno'] = $userno;
        $fields['enterpriseno'] = $enterpriseno;
        $fields['receiptusers'] = $tousers;
        $fields['from'] = 1;
        $fields['content'] = $content;
        $fields['title'] = $title;
        $fields['newscategoryid'] = $type;
        $fields['readusers'] = $tousers;
        $fields['status'] = Enum::getStatusType('Normal');
        $fields['attachmentids'] = $attachments;
        $fields['cancomment'] = 0;
        $feed_do_id = 20;
        $sys_do_id = 20;
        $fields['createtime'] = date(DATE_ISO8601);
        $fields['publishdate'] = date(DATE_ISO8601);
        $fields['updatetime'] = date(DATE_ISO8601);
        $id = News::addNews($fields);
        $dotype = 10;
        if (!$id) {
            return FALSE;
        }
        try {
            //添加操作日志 添加或者修改公告
            News::addSysLog($id, $dotype, $userno,$enterpriseno);
            //发新鲜事和系统消息
            $params = Params::getOneParams($enterpriseno, 1, $type);
            $category = array();
            $category['category'] = $params['paramname'];
            $user = Exterprise::getEmployeeInfo($userno, $enterpriseno);
            Doo::loadClass('Feed');
            Feed::publish_feed($id, 'newsid', $feed_do_id, $userno, $user->employeename, $enterpriseno, $tousers, '', '', 0);
            $target_userids = empty($tousers) ? 0 : News::removeTheSpecifiedValueArray($tousers, $userno);
            SysMessage::send_sysMessage($id, 'news', $sys_do_id, $userno, $user->employeename, $enterpriseno, '', $target_userids, $category);
            return true;
        } catch (Exception $exc) {
            $exc->getLine();
        }
    }
}
?>
