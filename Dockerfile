from jhbian/pider

ENV http_proxy 172.17.0.1:8118
ENV https_proxy 172.17.0.1:8118
ENV workdir /home/johans/pider/
EXPOSE 7000-7020:7000-7020
# set workdir
WORKDIR ${workdir}
