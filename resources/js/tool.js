Nova.booting((Vue, router, store) => {
  router.addRoutes([
    {
      name: 'test',
      path: '/test',
      component: require('./components/Tool'),
    },
  ])
})
