# simple forecasting
## average, mean of ts val
`meanf(ts, h)`, ts input time series, h forecast horizon.
## Naive, last ovservation
`naive(ts,h)`
## seasonal naive
`snaive(ts,h)`, last seasonal observation
```
beer2 <- window(ausbeer,start=1992,end=c(2007,4))
autoplot(beer2) +  autolayer(snaive(beer2, h=11)
 ```
 ## Drife
 `rwf(y,h, drift=T)`, line of first and last observation
#transformation
## box-cox
```
lambda <- BoxCox.lambda(elec)
autoplot(BoxCox(elec,lambda))
```
## bias-adjusted
back-transform if box-cox transformation is used

# residuals, judge if model is adequate
A good forecasting method will yield residuals with the following properties:
- The residuals are uncorrelated. If there are correlations between residuals, then there is information left in the residuals which should be used in computing forecasts.
- The residuals have zero mean. If the residuals have a mean other than zero, then the forecasts are biased.
- The residuals have constant variance.
- The residuals are normally distributed.
## residual acf (autocorrelation) 
`acf(ts, lag.max,plot=T/F)` can be used to see if ts is autocorrelated or not, high autocorreate has value on on one side, and 
normally closing to zero as lag increase, low correcllated ts tend to be on both side(up and down)
`checkresiduals(snaive(beer2))`
