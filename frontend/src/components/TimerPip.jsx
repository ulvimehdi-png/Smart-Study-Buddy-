import React, { useEffect, useMemo, useState } from "react";
import { createPortal } from "react-dom";
import Timer from "./Timer";
import { PictureInPicture2 } from "lucide-react"
import TimerButton from "./Button/TimerButton";
import { useTheme } from "@/lib/useTheme";

export default function TimerPip({className = ""}) {
  const [pipWindow, setPipWindow] = useState(null);
  const [pipContainer, setPipContainer] = useState(null);
  const { theme } = useTheme();

  const isSupported = useMemo(
    () => typeof window !== 'undefined' && 'documentPictureInPicture' in window,
    []
  );

  useEffect(() => {
    if (!pipWindow) {
      return;
    }

    if (theme === "dark") {
      pipWindow.document.documentElement.classList.add("dark");
    } else {
      pipWindow.document.documentElement.classList.remove("dark");
    }
    
  }, [theme, pipWindow]);

  useEffect(() => {
    if (!pipWindow) {
      return;
    }

    function handlePageHide() {
      setPipContainer(null);
      setPipWindow(null);
    }

    pipWindow.addEventListener("pagehide", handlePageHide);

    return () => {
      pipWindow.removeEventListener("pagehide", handlePageHide);
    };
  }, [pipWindow]);

  async function openPip() {
    try {
      if (pipWindow && !pipWindow.closed) {
        pipWindow.focus();
        return;
      }

      const nextPipWindow = await window.documentPictureInPicture.requestWindow({
        width: 350,
        height: 350,
      });

      // Inject CSS
      // https://dev.to/keni_97e44d198c12a5/how-i-implemented-picture-in-picture-in-react-with-full-code-tips-2aj3
      const target = nextPipWindow.document;
      Array.from(document.styleSheets).forEach((styleSheet) => {
        try {
          const rules = styleSheet.cssRules;
          const style = target.createElement('style');
          style.textContent = Array.from(rules)
            .map((rule) => rule.cssText)
            .join('\n');
          target.head.appendChild(style);
        } catch {
          const ownerNode = styleSheet.ownerNode;
          if (ownerNode?.tagName === 'LINK' && ownerNode.href) {
            const link = target.createElement('link');
            link.rel = 'stylesheet';
            link.href = ownerNode.href;
            target.head.appendChild(link);
          }
        }
      });
    
      // 3. Create a container for our React tree
      const pipDiv = nextPipWindow.document.createElement("div");
      pipDiv.setAttribute("id", "pip-root");
      nextPipWindow.document.body.append(pipDiv);
      setPipWindow(nextPipWindow);
      setPipContainer(pipDiv);

    } catch (error) {
      console.error("Error opening PiP:", error);
    }
  }

  return isSupported && (
    <>
      <TimerButton isPiP={false} onClick={openPip} IconComp={PictureInPicture2} className={className}>
        PiP
      </TimerButton>
      {pipContainer && createPortal(<Timer isPiP />, pipContainer)}
    </>
  );
}
