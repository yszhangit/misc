library(manipulate)
x <- seq(-25,25,0.1)
manipulate(
  plot(x, dlnorm(x,0, sdlog), type='l'),
  sdlog = slider(0.1, 2)
)

manipulate(
  plot(x, dnorm(x,mean,sd), type="l"),
  mean = slider(-10, 10, initial = 0),
  sd = slider(1,5)
)

x <- seq(-25,25,1)
manipulate(
  plot(x, dpois(x, lambda = lambda), type="l"),
  lambda = slider(1,10)
)
