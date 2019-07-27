<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header">更新我的订阅</div>

                    <div class="card-body">
                        <template>
                            <Form :model="formItem" :label-width="80">
                                <FormItem label="当前角色">
                                    <Select v-model="formItem.select">
                                        <Option value="1">普通用户</Option>
                                        <Option value="2">高级会员</Option>
                                    </Select>
                                </FormItem>
                                <FormItem label="预定时间">
                                    <RadioGroup v-model="formItem.radio">
                                        <Radio label="6">上午6:00</Radio>
                                        <Radio label="12" disabled>中午12:00</Radio>
                                        <Radio label="18" disabled>下午6:00</Radio>
                                    </RadioGroup>
                                </FormItem>
                                <FormItem label="精确时间">
                                     <TimePicker disabled :steps="[1, 15]" :disabled-hours="[0,1,2,3,4]" :value="value1" confirm format="HH:mm" type="time" placeholder="Select time" v-model="formItem.time"></TimePicker>
                                </FormItem>
                                <FormItem label="暂停接收">
                                    <i-switch v-model="formItem.switch" size="large">
                                        <span slot="open">On</span>
                                        <span slot="close">Off</span>
                                    </i-switch>
                                </FormItem>
                                <FormItem>
                                    <Button type="primary" disabled>点击更新</Button>
                                    <Button style="margin-left: 8px">加入高级订阅</Button>
                                </FormItem>
                            </Form>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>


<script>
    export default {
        data () {
            return {
                formItem: {
                    select: '1',
                    radio: '6',
                    time: '05:15:00',
                    switch: true,
                }
            }
        },
        methods: {
            handleSubmit (name) {
                this.$refs[name].validate((valid) => {
                    if (valid) {
                        this.$Message.success('Success!');
                    } else {
                        this.$Message.error('Fail!');
                    }
                })
            },
            handleReset (name) {
                this.$refs[name].resetFields();
            }
        }
    }
</script>
