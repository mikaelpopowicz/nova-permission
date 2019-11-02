Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'permission-builder',
      path: '/permission-builder',
      component: require('./components/Tool'),
    },
  ])
})
