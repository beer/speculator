<?php
class UserRow extends Pix_Table_Row
{
    public function preSave()
    {
        $this->updated_at = time();
    }

    public function avatar($type = null, $size= null) 
    {
        //for ipeen 
        if ($this->id == 11){
            return '/images/testIMG/default_user_editor.jpg';
        }
        
        // :NOTE: 沒有facebook 登入過的使用者，給預設頭像
        if (!$this->auth) {
            return '/images/testIMG/160X160.gif';
        }

        $fb_id = $this->auth->login_id;
        if (is_numeric($type)) {
            return 'https://graph.facebook.com/' . $fb_id .  "/picture?type=normal&width={$type}&height={$type}";
        }

        if ($type) {
            if(!$size){
                return 'https://graph.facebook.com/' . $fb_id .  '/picture?type=' . $type;
            }else{
                return 'https://graph.facebook.com/' . $fb_id .  '/picture?type=' . $type . '&width=' . $size . '&height=' . $size;
            }
        }
        return 'https://graph.facebook.com/' . $fb_id .  '/picture';
    }

    public function getUrl($type = null)
    {
        //:XXX: for 小編 
        if ($this->id == 11){
            return '#';
        }

        $id = $this->url ? $this->url : $this->id;
        switch ($type) {
        case 'footprint':
            if (defined('DEBUG')) {
                return "/u/{$id}/{$type}";
            }
            return "javascript:Ego.Mixins.Popup.warning({title:'即將開放', message:'功能即將開放，敬請期待！'});";
            break;
        case 'collection':
        case 'itinerary':
        case 'journey':
            return "/u/{$id}/{$type}";
            break;
        case 'collection_place':
        case 'collection_itinerary':
        case 'collection_journey':
            $collection = explode('_', $type);
            return "/u/{$id}/{$collection[0]}/{$collection[1]}";
            break;
        case 'zh_TW':
        case 'en_US':
            return "/user/setLang?user={$id}&lang={$type}&redirect=" . $_SERVER['REQUEST_URI'];
            break;
        default:
            return "/u/{$id}";
        }
    }

    /**
     * getUserCover 取得 user 封面 的 userPhotos row
     *
     * @access public
     * @return mixed
     */
    public function getUserCover()
    {
        $cover = $this->photos->search(array('type' => UserPhotos::TYPE_USERCOVER))->order('created_at DESC')->first();
        if ($cover) {
            return $cover;
        }

        return  null;
    }

    public function addUserWant($spot)
    {
        return $this->create_wants(array('spot_id' => $spot->id, 'category' => $spot->category, 'area' => $spot->parentmapping->parent_id));
    }
    public function addUserBeen($spot)
    {
        return $this->create_beens(array('spot_id' => $spot->id, 'category' => $spot->category, 'area' => $spot->parentmapping->parent_id));
    }
    public function isActivated()
    {
        return $this->is_activated === 2 ? true : false;
    }

    public function isSetSite()
    {
        return $this->is_activated === 1 ? true : false;
    }

    public function isSetInfo()
    {
        return $this->is_activated === 2 ? true : false;
    }

    public function setSite($site)
    {
        $site = strtolower($site);
        return $this->update(array('url' => $site, 'is_activated' => 1));
    }

    public function nickname()
    {
        return $this->nickname ? $this->nickname : $this->name;
    }

    public function getBeenCitys()
    {
        $beens = $this->beens;
        if (count($beens) > 0) {
            $ids = array();
            foreach ($beens as $been) {
                $ids[$been->spot->parentmapping->parent_id] = $been->spot->parentmapping->parent_id;
            }
            return Spot::search(1)->searchIn('id', $ids);
        }
        return null;
    }

    public function id()
    {
        if ($this->url) {
            return $this->url;
        }
        return $this->id;
    }

    public function preDelete()
    {
        if (sizeof($this->spotscore)) {
            foreach ($this->spotscore as $score) {
                if ($score->record == 3) {
                    $score->tagDelete();
                } else {
                    $score->delete();
                }
            }
        } // end if 
    }

    // category: 1:hotel 2:attractions 3:food 4:shop 5:station 6:event 7:area 8:customspot
    public function addCustomSpot($name, $category, $lat, $lng, $photo = null)
    {
        $user_id = $this->id;
        if (!is_numeric($category)) {
            $category = Spot::getCategory($category);
        }
        if ($category and $name and $address) {
            $row = Customspot::createRow();
            $row->lat = $lat;
            $row->lng = $lng;
            $row->category = 8; // 自定景點
            $row->type = $category;
            $row->name = $name;
            $row->user_id = $user_id;
            $row->save();
            if ($photo) {
                $user->uploadPhoto($photo, UserPhotos::TYPE_CUSTOMSPOT, $row->id);
            }
            return $row;
        }
        return null;
    }

    public function addFootprint($spot_id, $description, $file)
    {
        $user = $this;

        if(!$spot = Spot::find($spot_id)){
            return false;
        }

        //產生照片
        if ($file) {
            $photo = $user->uploadPhoto($file, UserPhotos::TYPE_CUSTOMSPOT, $spot_id);
        }

        $row = $user->create_footprints(array('spot_id' => $spot->id, 'photo_id' => $photo->id, 'description' => $description));
        return $row;
    }

    public function uploadPhoto($file, $type = null, $type_id = null)
    {
        $user = $this;
        $tempFile = $file['tmp_name'];
        $userDir = '/services/img/' . StdLib::getUserPath($user->id);
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $img = getimagesize($file['tmp_name']);
        if(!$img[0] || !$img[1]) {
            throw new Pix_Exception('Wrong Files');
        }
        $photo = $user->create_photos();
        if (!is_dir($userDir)) {
            mkdir($userDir, 0755, true);
        }
        $uniqid = str_pad($user->id, 10, "0", STR_PAD_LEFT) . str_pad($photo->id, 10, '0', STR_PAD_LEFT);
        $filename = StdLib::rand_uniqid($uniqid, false, false, 'EATGO') . '.' . $ext;
        $targetFile = $userDir . $filename;
        if ($img[0] < 155 || $img[1] < 155) {
            return false;
        } else {
            move_uploaded_file($tempFile, $targetFile);
            $photo->width = $img[0];
            $photo->height = $img[1];
            $photo->filename = $filename;
            if ($type) {
                $photo->type = $type;
                $photo->type_id = $type_id;
            }
            $photo->save();
            return $photo;
        }
    }

    public function setLang($lang = 'zh_TW')
    {
        $lang = $lang == 'en_US' ? User::LANG_ENUS : User::LANG_ZHTW;
        $this->update(['lang' => $lang]);
    }

    public function getLang()
    {
        return $this->lang == User::LANG_ENUS ? 'en_US' : 'zh_TW';
    }
}

class User extends EatgoTable
{
    const LANG_ZHTW = 1;
    const LANG_ENUS = 2;

    public function init()
    {
        $this->_name = 'users';
        $this->_rowClass = 'UserRow';

        $this->_primary = 'id';

        $this->_columns['id'] = array('type' => 'int', 'size' => 10, 'unsigned' => true, 'auto_increment' => true);
        $this->_columns['name'] = array('type' => 'varchar', 'size' => 30);
        $this->_columns['nickname'] = array('type' => 'varchar', 'size' => 50);
        $this->_columns['url'] = array('type' => 'varchar', 'size' => 30);
        $this->_columns['email'] = array('type' => 'varchar', 'size' => 100);
        $this->_columns['contact_email'] = array('type' => 'varchar', 'size' => 100);
        // lang: 0 => zh_TW, 1:zh_TW, 2:en_US
        $this->_columns['lang'] = array('type' => 'tinyint', 'size' => 1, 'unsigned' => true);
        $this->_columns['is_activated'] = array('type' => 'tinyint', 'size' => 1, 'unsigned' => true);
        $this->_columns['updated_at'] = array('type' => 'int', 'size' => 10, 'unsigned' => true);
        $this->_relations['info'] = array('rel' => 'has_one', 'type' => 'UserInfo', 'foreign_key' => 'id', 'delete' => true);
        $this->_relations['auth'] = array('rel' => 'has_one', 'type' => 'UserAuth', 'foreign_key' => 'id', 'delete' => true);
        $this->_relations['journeys'] = array('rel' => 'has_many', 'type' => 'Journeys', 'foreign_key' => 'user_id', 'delete' => true);
        $this->_relations['itinerarys'] = array('rel' => 'has_many', 'type' => 'Route', 'foreign_key' => 'user_id', 'delete' => true);
        $this->_relations['photos'] = array('rel' => 'has_many', 'type' => 'UserPhotos', 'foreign_key' => 'user_id', 'delete' => true);
        $this->_relations['routes'] = array('rel' => 'has_many', 'type' => 'Route', 'foreign_key' => 'user_id', 'delete' => true);
        $this->_relations['footprints'] = array('rel' => 'has_many', 'type' => 'Footprint', 'foreign_key' => 'user_id', 'delete' => true);
        $this->_relations['beens'] = array('rel' => 'has_many', 'type' => 'UserBeen', 'foreign_key' => 'user_id', 'delete' => true);
        $this->_relations['wants'] = array('rel' => 'has_many', 'type' => 'UserWant', 'foreign_key' => 'user_id', 'delete' => true);
        $this->_relations['favor_routes'] = array('rel' => 'has_many', 'type' => 'FavorRoute', 'foreign_key' => 'user_id', 'delete' => true);
        $this->_relations['favor_journeys'] = array('rel' => 'has_many', 'type' => 'FavorJourney', 'foreign_key' => 'user_id', 'delete' => true);
        $this->_relations['visitors'] = array('rel' => 'has_many', 'type' => 'Visitor', 'foreign_key' => 'user_id', 'delete' => true);

        $this->_relations['spotscore'] = array('rel' => 'has_many', 'type' => 'SpotScore', 'foreign_key' => 'user_id');
        $this->_relations['customspot'] = array('rel' => 'has_many', 'type' => 'Customspot', 'foreign_key' => 'user_id');
    }

    public static function validateUserSite($url, $me=null)
    {
        $url = strtolower($url);
        // 黑名單
        $spams = ConfigLib::spam('url', true);
        if (in_array($url, $spams)) {
            return array(
                'error' => 1,
                'message' => _('帳號已有人使用') // 黑名單
            );
        }

        $pattern = '/^[0-9]/';
        if (preg_match($pattern, $url)) {
            return array(
                'error' => 1,
                'message' => _('帳號需為英文字母開頭，3~15個英數字元')
            );
        }

        $pattern = '/^[A-Za-z0-9]*$/';
        if (!preg_match($pattern, $url)) {
            return array(
                'error' => 1,
                'message' => _('帳號需為3~15個英數字元')
            );
        }

        $pattern = '/^[A-Za-z0-9]*$/';
        if (!preg_match($pattern, $url)) {
            return array(
                'error' => 1,
                'message' => _('帳號長度有誤，請輸入3~15個英數字元')
            );
        }

        $pattern = '/^[A-Za-z]{1}[A-Za-z0-9]{2,14}$/';
        if (!preg_match($pattern, $url)) {
            return array(
                'error' => 1,
                'message' => _('帳號長度有誤，請輸入3~15個英數字元')
            );
        }

        $user = User::search(array('url' => $url));
        if (count($user) and ($user->first()->id != $me->id)) {
            return array(
                'error' => 1,
                'message' => _('帳號已有人使用')
            );
        }

        return array(
            'error' => 0,
            'message' => _('OK！此帳號可以使用') 
        );
    }
}
