        <div class="login-box">
            <div class="login-logo">
                <a href="#">选择大区或者门店</a>
            </div>
            <div class="login-box-body">
                <div>
                    <form action="/index/save-selected" method="get">
                        选择大区或门店：
                        <select name="organizational_structure_id_and_level">
                            <?php
                            if($data)
                            {
                                echo "<option value=" . $data['id'] . '_' . $data['level'] . ">{$data['name']}</option>";
                                if(isset($data['children']))
                                {
                                    foreach($data['children'] as $val){
                                        echo "<option value=" . $val['id'] . '_' . $val['level'] . ">|---{$val['name']}</option>";
                                        if(isset($val['children']))
                                        {
                                            foreach($val['children'] as $v)
                                            {
                                                echo "<option value=" . $v['id']. '_' . $v['level'] . ">&nbsp;&nbsp;&nbsp;|---{$v['name']}</option>";
                                            }
                                        }
                                    }
                                }
                            }
                            ?>
                        </select>
                        <input type="submit" value="提交" />
                    </form>
                </div>
            </div>
        </div>
