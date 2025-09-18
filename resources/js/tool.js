import Tool from './components/Tool.vue'
import FloatingVue from 'floating-vue'
import 'floating-vue/dist/style.css'
import vClickOutside from 'click-outside-vue3'

Nova.booting((app, store) => {
  app.use(FloatingVue)
  app.use(vClickOutside)
})

Nova.inertia('custom-email-sender', Tool)
