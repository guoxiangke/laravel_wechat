import Welcome from './components/Welcome'
import Page from './components/Page'
import SubscriptionUpdate from './components/SubscriptionUpdate'
import UsersIndex from './views/UsersIndex';

const routers = [
  {
      path: '/subscription/1',
      name: 'subscription.update',
      component: SubscriptionUpdate,
  },
  {
      path: '/index',
      name: 'welcome',
      component: Welcome,
      props: { title: "This is the SPA home" }
  },
  {
      path: '/spa-page',
      name: 'page',
      component: Page,
      props: {
          title: "This is the SPA Second Page",
          author : {
              name : "Fisayo Afolayan",
              role : "Software Engineer",
              code : "Always keep it clean"
          }
      }
  },
  {
      path: '/users',
      name: 'users.index',
      component: UsersIndex,
  },
];
export default routers;
