import Tool from './components/Tool.vue'
import FloatingVue from 'floating-vue'
import 'floating-vue/dist/style.css'
import vClickOutside from 'click-outside-vue3'

Nova.booting((app, router, store) => {
  app.use(FloatingVue)
  app.use(vClickOutside)

  router.addRoutes([
    {
      name: 'custom-email-sender',
      path: '/custom-email-sender',
      component: Tool,
    },
  ])
})
