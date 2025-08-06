import vue from 'vue'
import axios from 'axios'
import vueaxios from 'vue-axios'

vue.config.productionTip = false

const request = axios.create({
    baseURL: 'http://localhost/krisbamrungtham/Db/',
    })
    
vue.use(vueaxios, request)